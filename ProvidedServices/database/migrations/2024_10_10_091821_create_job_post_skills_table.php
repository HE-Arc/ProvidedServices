<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('job_post_skills', function (Blueprint $table) {
            $table->foreignId('job_post_id')->constrained('job_posts');
            $table->foreignId('skill_id')->constrained('skills');
            $table->primary(['job_post_id', 'skill_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_skills');
    }
};
