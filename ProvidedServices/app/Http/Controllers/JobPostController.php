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
        $user = Auth::user();

        $jobPosts = JobPost::with('skills', 'client')
            ->when($user && $user->role === 'provider', function ($query) use ($user) {
                $query->whereDoesntHave('applications', function ($q) use ($user) {
                    $q->where('provider_id', $user->id)
                    ->where('status', 'refused');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($jobPosts);
    }

    // Postuler à une offre d'emploi
    public function storeApply($job_post_id)
    {
        $user = Auth::user();

        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Vous devez être un prestataire pour postuler.'], 403);
        }

        Application::updateOrCreate(
            ['job_post_id' => $job_post_id, 'provider_id' => $user->id],
            ['status' => 'on hold']
        );

        return response()->json(['message' => 'Candidature envoyée avec succès.'], 200);
    }

    public function appliedJob()
    {
        $user = Auth::user();

        $appliedJobs = Application::where('provider_id', $user->id)
            ->pluck('job_post_id');

        return response()->json($appliedJobs);
    }


    public function destroyApply($job_post_id)
    {
        $user = Auth::user();

        Application::where('job_post_id', $job_post_id)
            ->where('provider_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Candidature retirée avec succès.'], 200);
    }

    public function create()
    {
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

        return response()->json(['job_post' => $jobPost, 'message' => 'Annonce créée avec succès !'], 201);
    }

    public function clientJobPosts()
    {
        $clientId = Auth::id();
        $jobPosts = JobPost::where('client_id', $clientId)->with('skills')->orderBy('created_at', 'desc')->get();
        return response()->json($jobPosts);
    }

    public function ProviderApplications()
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un prestataire
        if ($user->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Récupérer les candidatures avec les annonces et leurs compétences
        $applications = Application::where('provider_id', $user->id)
                        ->with(['jobPost.skills', 'jobPost.client'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($applications);
    }

    public function chooseProvider(Request $request, $jobId)
{
    // Récupérer l'ID du prestataire depuis la requête
    $providerId = $request->input('providerId');

    if (!$providerId) {
        return response()->json(['message' => 'Provider ID is required.'], 400);
    }

    $jobPost = JobPost::findOrFail($jobId);
    $provider = User::findOrFail($providerId);

    // Mettre à jour le statut du prestataire pour ce job
    $updateStatus = $jobPost->applications()
        ->where('provider_id', $provider->id)
        ->update(['status' => 'accepted']);

    if (!$updateStatus) {
        return response()->json(['message' => 'Failed to update the application status.'], 500);
    }

    // Envoyer un e-mail de notification
    try {
        Mail::to($provider->email)->send(new ApplicationAcceptedMail($provider, $jobPost));
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send email.'], 500);
    }

    return response()->json(['message' => 'Prestataire sélectionné et notification envoyée.']);
}


    public function jobApplications($job_post_id)
    {
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $applications = Application::where('job_post_id', $job_post_id)
            ->with('provider')
            ->get();

        return response()->json($applications);
    }

    public function updateApplicationStatus(Request $request, $jobPostId, $providerId)
    {
        $user = Auth::user();

        // Vérifiez que l'utilisateur est un client
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifiez que l'application existe
        $application = Application::where('job_post_id', $jobPostId)
            ->where('provider_id', $providerId)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Vérifiez que le client est le propriétaire du job
        if ($application->jobPost->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mettez à jour directement la base de données avec `update`
        Application::where('job_post_id', $jobPostId)
            ->where('provider_id', $providerId)
            ->update(['status' => $request->input('status')]);

        // Envoyez un email si le statut est "accepted"
        if ($request->input('status') === 'accepted') {
            $provider = $application->provider;
            $jobPost = $application->jobPost;
            Mail::to($provider->email)->send(new ApplicationAcceptedMail($provider, $jobPost));
        }

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function destroy($id)
    {
        $jobPost = JobPost::findOrFail($id);

        if (Auth::id() !== $jobPost->client_id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette annonce.'], 403);
        }

        // Supprimer les relations
        $jobPost->skills()->detach();
        Application::where('job_post_id', $jobPost->id)->delete();

        $jobPost->delete();

        return response()->json(['message' => 'Annonce supprimée avec succès.'], 200);
    }
}