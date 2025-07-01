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
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot(['enrolled_at', 'paid', 'is_favorite', 'receipt_path', 'payment_amount', 'admin_share', 'teacher_share', 'payment_date'])
            ->withTimestamps();
    }

    // public function skills()
    // {
    //     return $this->belongsToMany(Skill::class, 'skills_courses');
    // }
}
