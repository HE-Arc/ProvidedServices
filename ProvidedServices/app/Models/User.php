<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'email',
        'password_hash',
        'salt',
        'first_name',
        'last_name',
        'genre',
        'role',
        'cv_path',
        'picture'
    ];

    protected $hidden = [
        'password_hash', 'salt', 'remember_token',
    ];

    public function skills()
    {
        return $this->belongsToMany(Skills::class, 'user_skills', 'user_id', 'skill_id');
    }
    public function appliedJobs()
    {
        return $this->hasMany(Application::class, 'provider_id');
    }
}
