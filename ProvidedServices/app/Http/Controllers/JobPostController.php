<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPost;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Mail\ApplicationAcceptedMail;
use Illuminate\Support\Facades\Mail;

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

        // Récupérer les annonces auxquelles l'utilisateur a postulé, avec les informations des compétences et du job post
        $appliedJobs = Application::where('provider_id', $user->id)->pluck("job_post_id");

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

    public function getClientJobPosts()
    {
        $clientId = Auth::id();
        $jobPosts = JobPost::where('client_id', $clientId)->with('skills')->get();
        return response()->json($jobPosts);
    }

    public function getProviderDashboardApplications()
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un prestataire
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Récupérer les candidatures avec les annonces et leurs compétences
        $applications = Application::where('provider_id', $user->id)
                        ->with(['jobPost.skills', 'jobPost.client']) // Charger les relations jobPost et skills
                        ->get();

        return response()->json($applications);
    }

    public function chooseProvider(Request $request, $jobPostId)
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un client
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifiez que l'annonce appartient à ce client
        $jobPost = JobPost::where('id', $jobPostId)->where('client_id', $user->id)->first();
        if (!$jobPost) {
            return response()->json(['message' => 'Job post not found or unauthorized'], 404);
        }

        $providerId = $request->input('providerId');

        // Mettez à jour le statut du prestataire sélectionné
        Application::where('job_post_id', $jobPostId)
            ->where('provider_id', $providerId)
            ->update(['status' => 'accepted']);

        return response()->json(['message' => 'Provider selected successfully']);
    }

    public function getJobApplications($jobPostId)
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un client
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifiez que l'annonce appartient à ce client
        $jobPost = JobPost::where('id', $jobPostId)->where('client_id', $user->id)->first();
        if (!$jobPost) {
            return response()->json(['message' => 'Job post not found or unauthorized'], 404);
        }

        // Récupérer les candidatures avec les informations des prestataires
        $applications = Application::where('job_post_id', $jobPostId)
            ->with('provider') // Charge les données du prestataire
            ->get();

        return response()->json($applications);
    }

    public function updateApplicationStatus(Request $request, $applicationId)
    {
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application = Application::findOrFail($applicationId);

        if ($application->jobPost->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application->status = $request->input('status');
        $application->save();

        if ($application->status === 'accepted') {
            $provider = $application->provider;
            Mail::to($provider->email)->send(new ApplicationAcceptedMail($application));
        }

        return response()->json(['message' => 'Status updated successfully']);
    }
}
