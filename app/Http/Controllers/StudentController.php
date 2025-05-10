<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\ResponseTrait;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use App\Mail\SendOtpMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class StudentController extends Controller
{
    use ResponseTrait;
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:students',
            'email' => 'required|email|max:255|unique:teachers',
            'password' => 'required|string|min:6',
            'bio' => 'nullable|string',
            'age' => 'required|integer',
            'gender' => 'required|in:male,female',
            'specialization' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $otp = mt_rand(100000, 999999);;
        $expiresAt = Carbon::now()->addMinutes(10);

        $student = Student::create([
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'age' => $request->age,
            'gender' => $request->gender,
            'specialization' => $request->specialization,
            'email_otp' => $otp,
            'email_otp_expires_at' => $expiresAt,
            'is_email_verified' => false,
        ]);

        Mail::to($student->email)->send(new SendOtpMail($otp));

        return $this->returnSuccess("Account created successfully. Please check your email for the verification code.");;
    }


    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $student = Student::where('email', $request->email)->first();

        if (!$student) {
            return $this->returnError("Email not found");
        }

        if ($student->is_email_verified) {
            return $this->returnError("Email already verified");
        }

        if (
            $student->email_otp !== $request->otp ||
            Carbon::now()->greaterThan($student->email_otp_expires_at)
        ) {
            return $this->returnError("Invalid or expired OTP");
        }

        $student->update([
            'is_email_verified' => true,
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ]);

        return $this->returnSuccess("Email verified successfully");
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:students,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }

        $student = Student::where('email', $request->email)->first();

        if (!Hash::check($request->password, $student->password)) {
            return $this->returnError("Incorrect password");
        }

        $token = JWTAuth::fromUser($student);
        $student->api = $token;

        return $this->returnData("you are logged-in successfully", "student", $student);
    }

    public function logout(Request $request)
    {
        try {
            auth("api-student")->logout();
            return $this->returnSuccess("you are logged-out successfully");
        } catch (JWTException $e) {
            return $this->returnError("there were smth wrong");
        }
    }

    public function me()
    {
        return $this->returnData("about me:", "", auth("api-student")->user());;
    }
}
