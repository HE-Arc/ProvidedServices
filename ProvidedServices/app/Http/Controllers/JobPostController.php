<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;

class JobPostController extends Controller
{
    public function create()
    {
        // Retourne la vue où l'utilisateur peut créer une offre
        return view('job_post');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'skills' => 'required|array',
        ]);

        $user_id = Auth::id();

        // Créer l'offre d'emploi
        $jobPost = JobPost::create([
            'client_id' => $user_id,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Associer les compétences (skills) à l'offre d'emploi
        $jobPost->skills()->sync($request->skills);

        return response()->json(['job_post' => $jobPost, 'message' => 'Job post created successfully!'], 201);
    }

    public function getClientJobPosts()
    {
        $clientId = Auth::id();
        //Log::info("ID de l'utilisateur connecté : " . $clientId);

        if (!$clientId) {
            //Log::error("Aucun utilisateur n'est authentifié.");
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        try {
            $jobPosts = JobPost::where('client_id', $clientId)->with('skills')->get();
            //Log::info("Nombre d'annonces récupérées pour l'utilisateur {$clientId} : " . count($jobPosts));
            return response()->json($jobPosts);
        } catch (\Exception $e) {
            //Log::error("Erreur lors de la récupération des annonces : " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des annonces'], 500);
        }
    }



        public function getProviderApplications()
        {
            $providerId = Auth::id(); // ID du prestataire connecté
            $applications = Application::where('provider_id', $providerId)
                ->with(['jobPost' => function ($query) {
                    $query->with('skills');
                }])->get();

            return response()->json($applications);
        }

}
