<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Skills;

class JobPostCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_a_logged_in_user_can_create_a_job_post()
    {
        $user = new \App\Models\User([
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password'),
            'salt' => 'abc123',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'genre' => 'male',
            'role' => 'client',
        ]);
        $user->save();

        $this->actingAs($user);

        $skill1 = Skills::create(['name' => 'Laravel']);
        $skill2 = Skills::create(['name' => 'API']);
        $skill3 = Skills::create(['name' => 'PHP']);

        $response = $this->post('/api/job-posts', [
            'title' => 'Développeur Laravel',
            'description' => 'Créer une API REST avec Laravel',
            'skills' => [$skill1->id, $skill2->id, $skill3->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('job_posts', [
            'title' => 'Développeur Laravel',
        ]);
    }

    /** @test */
    public function test_a_guest_user_cannot_create_a_job_post()
    {
        $skill1 = Skills::create(['name' => 'Laravel']);
        $skill2 = Skills::create(['name' => 'API']);
        $skill3 = Skills::create(['name' => 'PHP']);

        $response = $this->post('/api/job-posts', [
            'title' => 'Tentative par un invité',
            'description' => 'Ceci devrait échouer',
            'skills' => [$skill1->id, $skill2->id, $skill3->id],
        ]);

        $response->assertRedirect('/login');
    }
}
