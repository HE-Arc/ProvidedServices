<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_post_id',
        'provider_id',
        'status',
    ];

    // Relation avec JobPost
    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    // Relation avec User (prestataire)
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
