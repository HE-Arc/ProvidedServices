<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPost;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class JobPostController extends Controller
{
    public function index()
    {
        $jobPosts = JobPost::with('skills', 'client')->get();
        return response()->json($jobPosts);
    }

    // Postuler à une offre d'emploi
    public function apply($id)
    {
        // Vérifier que l'utilisateur est un provider
        $user = Auth::user();
        
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'You must be a provider to apply for a job.'], 403);
        }

        $jobPost = JobPost::findOrFail($id);
        
        // Créer une nouvelle application pour ce job
        $application = Application::create([
            'job_post_id' => $jobPost->id,
            'provider_id' => $user->id,
            'status' => 'on hold', // statut initial de l'application
        ]);

        return response()->json(['message' => 'Successfully applied for the job!'], 200);
    }

    public function appliedJob()
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un prestataire
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Récupérez les jobs auxquels l'utilisateur a postulé
        $appliedJobs = Application::where('provider_id', $user->id)->pluck('job_post_id');

        return response()->json($appliedJobs);
    }

    public function unapply($id)
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un prestataire
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifiez si l'application existe
        $application = Application::where('job_post_id', $id)
                                ->where('provider_id', $user->id)
                                ->first();

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Supprimez l'application
        $application->delete();

        return response()->json(['message' => 'Successfully removed your application!'], 200);
    }

    public function create()
    {
        // Retourne la vue où l'utilisateur peut créer une offre
        return view('job_post');
    }

    public function getProfilePicture($id)
    {
        $user = User::findOrFail($id);
        if ($user->picture) {
            return response()->json(['profilePictureUrl' => asset("storage/{$user->picture}")]);
        }
        return response()->json(['profilePictureUrl' => null]);
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
