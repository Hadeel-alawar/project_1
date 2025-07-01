<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Models\Teacher;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Routing\RouteRegistrar;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('teacher')->group(function () {
    Route::post('register', [TeacherController::class, 'register']);
    Route::post('verify-email', [TeacherController::class, 'verifyEmailOtp']);
    Route::post('login', [TeacherController::class, 'login']);

    Route::middleware("auth.teacher")->group(function () {
        Route::get('logout', [TeacherController::class, 'logout']);
        Route::middleware('teacherVerified')->group(function () {
            Route::get('view_profile', [TeacherController::class, 'viewProfile']);
            Route::post("create_course", [TeacherController::class, "createCourse"]);
            Route::post("update_profile", [TeacherController::class, "updateProfile"]);
            Route::get("view_teacher's_courses", [TeacherController::class, "myCourses"]);
            Route::get("view_course_details/{id}", [TeacherController::class, "showCourse"]);
            Route::post("add_to_course/{course_id}", [TeacherController::class, "addContentToCourse"]);
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
        });
    });
});
