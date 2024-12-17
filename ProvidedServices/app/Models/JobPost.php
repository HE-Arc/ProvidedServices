<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Skills;

class JobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'client_id',
    ];

    public function skills()
    {
        return $this->belongsToMany(Skills::class, 'job_post_skills', 'job_post_id', 'skill_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');  
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
