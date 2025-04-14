<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Skills;

class UserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_and_create_a_job_post()
    {
        // Étape 1 : inscription via API
        $response = $this->post('/api/register', [
            'email' => 'flow@example.com',
            'password' => 'password',
            'first_name' => 'Flow',
            'last_name' => 'Test',
            'gender' => 'male', // important : 'male' et pas 'H'
            'role' => 'client',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Inscription réussie',
        ]);

        // Étape 2 : récupérer l'utilisateur
        $user = User::where('email', 'flow@example.com')->first();
        $this->assertNotNull($user);

        // Étape 3 : simuler l'utilisateur connecté
        $this->actingAs($user);

        // Étape 4 : créer une compétence et une offre
        $skill = Skills::create(['name' => 'Laravel']);

        $response = $this->post('/api/job-posts', [
            'title' => 'Nouvelle mission',
            'description' => 'Développement Laravel',
            'skills' => [$skill->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('job_posts', [
            'title' => 'Nouvelle mission',
        ]);

        // Étape 5 : vérifier qu'on peut accéder à une route protégée
        $response = $this->get('/api/user');
        $response->assertOk();
        $response->assertJson([
            'email' => 'flow@example.com',
        ]);
    }
}
