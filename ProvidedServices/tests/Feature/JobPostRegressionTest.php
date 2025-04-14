<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Skills;

class JobPostRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_post_cannot_be_created_without_title()
    {
        $user = new User([
            'email' => 'regression@example.com',
            'password_hash' => bcrypt('password'),
            'salt' => 'xyz789',
            'first_name' => 'Reg',
            'last_name' => 'Test',
            'genre' => 'male',
            'role' => 'client',
        ]);
        $user->save();

        $this->actingAs($user);

        $skill = Skills::create(['name' => 'Laravel']);

        $response = $this->postJson('/api/job-posts', [
            // 'title' est manquant ici, volontairement
            'description' => 'Job sans titre',
            'skills' => [$skill->id],
        ]);

        $response->assertStatus(422); // ou autre selon ta logique de validation
        $response->assertJsonValidationErrors('title');
    }
}
