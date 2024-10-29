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
        'gender',
        'role',
        'description'
    ];

    protected $hidden = [
        'password_hash', 'salt', 'remember_token',
    ];
}
