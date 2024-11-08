<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPost;
use Illuminate\Support\Facades\Auth;

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
}
