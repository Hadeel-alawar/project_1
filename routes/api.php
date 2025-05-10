<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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
            Route::get('me', [TeacherController::class, 'me']);
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
