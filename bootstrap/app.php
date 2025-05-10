<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.teacher' => \App\Http\Middleware\AuthTeacher::class,
            'auth.student' => \App\Http\Middleware\AuthStudent::class,
            'teacherVerified' => \App\Http\Middleware\verifyTeacherEmail::class,
            'studentVerified' => \App\Http\Middleware\verifyStudentEmail::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
