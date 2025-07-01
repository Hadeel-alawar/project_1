<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Choice extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = ["question_id" , "choice_text" , "is_correct"];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function is_correct()
    {
        return $this->boolean('is_correct'); 
    }
}
