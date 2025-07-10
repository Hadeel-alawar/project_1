<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Quiz extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = ["course_id", "title", "description"];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
