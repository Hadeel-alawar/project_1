<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Teacher;

class Student extends Authenticatable implements JWTSubject
{
    protected $fillable = ["full_name", "user_name", "email", "password", "bio", "age", "gender", "specialization", "email_otp", "email_otp_expires_at", "is_email_verified"];

    protected $hidden = [
        'password',
        "email_otp",
        "email_otp_expires_at"
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'student_teacher', 'student_id', 'teacher_id');
    }
}
