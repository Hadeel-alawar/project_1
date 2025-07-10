<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\ResponseTrait;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Nette\Utils\Random;
use App\Mail\SendOtpMail;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseVideo;
use App\Models\Quiz;

class TeacherController extends Controller
{
    use ResponseTrait;
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:teachers',
            'email' => 'required|email|max:255|unique:teachers',
            'password' => 'required|string|min:6',
            'bio' => 'nullable|string',
            'age' => 'required|integer',
            'gender' => 'required|in:male,female',
            'specialization' => 'required|string',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5048',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $otp = mt_rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        DB::beginTransaction();

        $teacher = Teacher::create([
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'age' => $request->age,
            'gender' => $request->gender,
            'specialization' => $request->specialization,
            'cv' => '',
            'email_otp' => $otp,
            'email_otp_expires_at' => $expiresAt,
            'is_email_verified' => false,
        ]);

        $file = $request->file('cv');
        $fileName = $teacher->id . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($teacher->id, $fileName, 'teacher_cv');

        $teacher->cv = Storage::disk('teacher_cv')->url($path);
        $teacher->save();

        DB::commit();

        Mail::to($teacher->email)->send(new SendOtpMail($otp));

        return $this->returnSuccess("Account created successfully. Please check your email for the verification code.");
    }


    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $teacher = Teacher::where('email', $request->email)->first();

        if (!$teacher) {
            return $this->returnError("Email not found");
        }

        if ($teacher->is_email_verified) {
            return $this->returnError("Email already verified");
        }

        if (
            $teacher->email_otp !== $request->otp ||
            Carbon::now()->greaterThan($teacher->email_otp_expires_at)
        ) {
            return $this->returnError("Invalid or expired OTP");
        }

        $teacher->update([
            'is_email_verified' => true,
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ]);

        return $this->returnSuccess("Email verified successfully");
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:teachers,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $teacher = Teacher::where('email', $request->email)->first();

        if (!Hash::check($request->password, $teacher->password)) {
            return $this->returnError("Incorrect password");
        }

        $token = JWTAuth::fromUser($teacher);
        $teacher->api = $token;
        return $this->returnData("login successfully", "teacher", $teacher);
    }

    public function logout(Request $request)
    {
        try {
            auth("api-teacher")->logout();
            return $this->returnSuccess("you are logged-out successfully");
        } catch (JWTException $e) {
            return $this->returnError("there were smth wrong");
        }
    }

    public function viewProfile()
    {
        return $this->returnData("about me:", " ", auth('api-teacher')->user());
    }

    public function createCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi|max:50000',
            'materials.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $teacher = auth('api-teacher')->user();

        DB::beginTransaction();

        try {
            $thumbnailUrl = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailName = uniqid('thumb_') . '.' . $thumbnailFile->getClientOriginalExtension();
                $path = $thumbnailFile->storeAs($teacher->id, $thumbnailName, 'course_thumbnails');
                $thumbnailUrl = Storage::disk('course_thumbnails')->url($path);
            }

            $course = Course::create([
                'teacher_id' => $teacher->id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'thumbnail_path' => $thumbnailUrl,
                'category_id' => $request->category_id,
            ]);

            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoName = uniqid('video_') . '.' . $video->getClientOriginalExtension();
                    $videoPath = $video->storeAs($course->id, $videoName, 'course_videos');
                    $videoUrl = Storage::disk('course_videos')->url($videoPath);

                    CourseVideo::create([
                        'course_id' => $course->id,
                        'video_path' => $videoUrl,
                    ]);
                }
            }

            if ($request->hasFile('materials')) {
                foreach ($request->file('materials') as $material) {
                    $materialName = uniqid('material_') . '.' . $material->getClientOriginalExtension();
                    $materialPath = $material->storeAs($course->id, $materialName, 'course_materials');
                    $materialUrl = Storage::disk('course_materials')->url($materialPath);

                    CourseMaterial::create([
                        'course_id' => $course->id,
                        'file_path' => $materialUrl,
                    ]);
                }
            }

            DB::commit();
            return $this->returnSuccess("Course created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError("Something went wrong: " . $e->getMessage());
        }
    }

    public function updateProfile(Request $request)
    {
        $teacher = auth('api-teacher')->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'user_name' => 'sometimes|string|max:255|unique:teachers,user_name,' . $teacher->id,
            'email' => 'sometimes|email|max:255|unique:teachers,email,' . $teacher->id,
            'bio' => 'nullable|string',
            'age' => 'nullable|integer',
            'gender' => 'in:male,female',
            'specialization' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $fileName = $teacher->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($teacher->id, $fileName, 'teacher_cv');
            $validated['cv'] = Storage::disk('teacher_cv')->url($path);
        }

        Teacher::where('id', $teacher->id)->update($validated);

        return $this->returnSuccess("Profile updated successfully.");
    }

    public function myCourses()
    {
        $teacher = auth('api-teacher')->user();
        $courses = $teacher->courses()
            ->with(['videos', 'materials'])
            ->get();

        return $this->returnData("My Courses", "courses", $courses);
    }

    public function showCourse($id)
    {
        $teacher = auth('api-teacher')->user();

        $course = $teacher->courses()->with(['videos', 'materials'])->find($id);

        if (!$course) {
            return $this->returnError("Course not found or you do not own it.");
        }

        return $this->returnData("Course Details", "course", $course);
    }

    public function addContentToCourse(Request $request, $id)
    {
        $teacher = auth('api-teacher')->user();

        $course = $teacher->courses()->findOrFail($id);
        if (!$course) {
            return $this->returnError("Course not found or you do not own it.");
        }

        $request->validate([
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi|max:50000',
            'materials.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10000',
        ]);

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $videoName = uniqid('video_') . '.' . $video->getClientOriginalExtension();
                $videoPath = $video->storeAs($course->id, $videoName, 'course_videos');
                $videoUrl = Storage::disk('course_videos')->url($videoPath);

                CourseVideo::create([
                    'course_id' => $course->id,
                    'video_path' => $videoUrl,
                ]);
            }
        }

        if ($request->hasFile('materials')) {
            foreach ($request->file('materials') as $material) {
                $materialName = uniqid('material_') . '.' . $material->getClientOriginalExtension();
                $materialPath = $material->storeAs($course->id, $materialName, 'course_materials');
                $materialUrl = Storage::disk('course_materials')->url($materialPath);

                CourseMaterial::create([
                    'course_id' => $course->id,
                    'file_path' => $materialUrl,
                ]);
            }
        }

        return $this->returnSuccess("Content added to course successfully.");
    }

    public function createQuiz(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.choices' => 'required|array|min:2',
            'questions.*.choices.*.choice_text' => 'required|string',
            'questions.*.choices.*.is_correct' => 'required|boolean',
        ]);

        $quiz = Quiz::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        foreach ($request->questions as $q) {
            $question = $quiz->questions()->create([
                'question_text' => $q['question_text'],
            ]);

            foreach ($q['choices'] as $choice) {
                $question->choices()->create([
                    'choice_text' => $choice['choice_text'],
                    'is_correct' => $choice['is_correct'],
                ]);
            }
        }

        return $this->returnSuccess("Quiz created successfully.");
    }

    public function quizStats($quizId)
    {
        $quiz = Quiz::with('questions.choices', 'questions.answers')->findOrFail($quizId);

        $result = [];
        $totalCorrectAnswers = 0;
        $totalAnswers = 0;

        foreach ($quiz->questions as $question) {
            $answers = $question->answers;

            $questionStats = [
                'question' => $question->question_text,
                'total_answers' => $answers->count(),
                'correct_answers' => 0,
                'percentage_correct' => 0,
            ];

            foreach ($answers as $answer) {
                if ($answer->choice->is_correct) {
                    $questionStats['correct_answers']++;
                }
            }

            $questionStats['percentage_correct'] = $answers->count() > 0
                ? round(($questionStats['correct_answers'] / $answers->count()) * 100, 2)
                : 0;

            $totalCorrectAnswers += $questionStats['correct_answers'];
            $totalAnswers += $answers->count();

            $result['questions'][] = $questionStats;
        }

        $result['overall_percentage'] = $totalAnswers > 0
            ? round(($totalCorrectAnswers / $totalAnswers) * 100, 2)
            : 0;

        return response()->json($result);
    }

    public function delete_course($id)
    {
        $teacher = auth('api-teacher')->user();

        $course = $teacher->courses()->find($id);

        if (!$course) {
            return $this->returnError("Course not found or you do not own it.");
        }

        $course->delete();

        return $this->returnSuccess("Course deleted successfully.");
    }

    public function deleteCourseVideo($videoId)
    {
        $teacher = auth('api-teacher')->user();

        $video = \App\Models\CourseVideo::find($videoId);

        if (!$video || $video->course->teacher_id !== $teacher->id) {
            return $this->returnError("Video not found or unauthorized.");
        }

        $videoPath = str_replace(env('APP_URL') . '/', '', $video->video_path);
        if (file_exists(public_path($videoPath))) {
            unlink(public_path($videoPath));
        }

        $video->delete();

        return $this->returnSuccess("Video deleted successfully.");
    }

    public function deleteCourseMaterial($materialId)
    {
        $teacher = auth('api-teacher')->user();

        $material = \App\Models\CourseMaterial::find($materialId);

        if (!$material || $material->course->teacher_id !== $teacher->id) {
            return $this->returnError("Material not found or unauthorized.");
        }

        $materialPath = str_replace(env('APP_URL') . '/', '', $material->file_path);
        if (file_exists(public_path($materialPath))) {
            unlink(public_path($materialPath));
        }

        $material->delete();

        return $this->returnSuccess("Material deleted successfully.");
    }
}
