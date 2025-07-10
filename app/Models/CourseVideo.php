<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CourseVideo extends Authenticatable implements JWTSubject
{

    use Notifiable;
    protected $fillable = ['course_id', 'video_path'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
