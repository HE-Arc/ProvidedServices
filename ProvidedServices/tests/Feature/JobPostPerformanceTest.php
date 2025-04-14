<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Skills;

class JobPostPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_post_creation_is_performant()
    {
        $user = new User([
            'email' => 'perf@example.com',
            'password_hash' => bcrypt('password'),
            'salt' => 'perfSalt',
            'first_name' => 'Speedy',
            'last_name' => 'Gonzales',
            'genre' => 'male',
            'role' => 'client',
        ]);
        $user->save();

        $this->actingAs($user);

        $skill = Skills::create(['name' => 'FastSkill']);

        $start = microtime(true);

        $response = $this->post('/api/job-posts', [
            'title' => 'Ultra rapide',
            'description' => 'Ce post doit être rapide à créer',
            'skills' => [$skill->id],
        ]);

        $duration = microtime(true) - $start;

        $response->assertStatus(201);
        $this->assertLessThan(0.5, $duration, "Job post creation took too long: {$duration} seconds");
    }
}
