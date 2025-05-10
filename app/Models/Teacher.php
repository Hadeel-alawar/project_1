<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Student;
use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = ["full_name" , "user_name" , "email" , "password" , "bio" , "age" , "gender" , "specialization" , "cv" , "email_otp" , "email_otp_expires_at" , "is_email_verified"];

    protected $hidden = [
        'password', "email_otp" , "email_otp_expires_at"
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_teacher', 'teacher_id', 'student_id');
    }
}
