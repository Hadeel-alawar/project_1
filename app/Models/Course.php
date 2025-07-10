<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Course extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'price',
        'thumbnail_path',
        'category_id'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')->withTimestamps();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(Student::class, 'favorites')->withTimestamps();
    }
}
