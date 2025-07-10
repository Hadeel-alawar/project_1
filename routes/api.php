<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Models\Teacher;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Routing\RouteRegistrar;


Route::prefix('teacher')->group(function () {
    Route::post('register', [TeacherController::class, 'register']);
    Route::post('verify-email', [TeacherController::class, 'verifyEmailOtp']);
    Route::post('login', [TeacherController::class, 'login']);

    Route::middleware("auth.teacher")->group(function () {
        Route::get('logout', [TeacherController::class, 'logout']);
        Route::middleware('teacherVerified')->group(function () {
            Route::get('view-profile', [TeacherController::class, 'viewProfile']);
            Route::post("create-course", [TeacherController::class, "createCourse"]);
            Route::post("update-profile", [TeacherController::class, "updateProfile"]);
            Route::get("view-teacher-courses", [TeacherController::class, "myCourses"]);
            Route::get("view-course-details/{id}", [TeacherController::class, "showCourse"]);
            Route::post("add-to-course/{course_id}", [TeacherController::class, "addContentToCourse"]);
            Route::post("delete-course/{id}",[TeacherController::class,"delete_course"]);
            Route::post("delete-video-from-course{video_id}",[TeacherController::class,"deleteCourseVideo"]);
            Route::post("delete-material-from-course/{material_id}",[TeacherController::class,"deleteCourseMaterial"]);
            Route::post("create-quiz",[TeacherController::class,"createQuiz"]);
            Route::post("calculate-degree",[TeacherController::class,"quizStats"]);
        });
    });
});


Route::prefix('student')->group(function () {
    Route::post('register', [StudentController::class, 'register']);
    Route::post('login', [StudentController::class, 'login']);
    Route::post('verify-email', [StudentController::class, 'verifyEmailOtp']);
    Route::middleware("auth.student")->group(function () {
        Route::get('logout', [StudentController::class, 'logout']);
        Route::middleware('studentVerified')->group(function () {
            Route::get('me', [StudentController::class, 'me']);
            Route::post("enroll",[EnrollmentController::class,"enrollInCourse"]);
            Route::get("browseCourses",[StudentController::class,"getMyCourses"]);
            Route::post("addToFav",[StudentController::class,"addToFavorites"]);
            Route::get("viewProfile",[StudentController::class,"profile"]);
            Route::post("updateProfile",[StudentController::class,"updateProfile"]);
            Route::get("viewCourseDet/{id}",[StudentController::class,"courseDetails"]);
            Route::get("viewTeacherPro/{teacherId}",[StudentController::class,"teacherProfile"]);
            Route::get("browseTacherCourses/{teacherId}",[StudentController::class,"teacherCourses"]);
            
        });
    });
});
