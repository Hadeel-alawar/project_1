<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CourseMaterial extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = ['course_id', 'file_path'];

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
