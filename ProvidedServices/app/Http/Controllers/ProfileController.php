<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    // Affiche le profil de l'utilisateur
    public function show($id)
    {
        // Vérifiez si l'utilisateur est authentifié
        if (Auth::check()) {
            $user = User::findOrFail($id);
            return view('profile', ['user' => $user, 'authUserId' => Auth::id()]);
        }

        // Si non authentifié, redirigez vers la page de connexion
        return redirect()->route('login');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return response()->json(['user' => $user], 200);
    } 

    public function uploadCv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Validation du fichier
        $request->validate([
            'cv' => 'required|mimes:pdf|max:2048' // Limité à 2 MB pour les PDF
        ]);

        if ($user->cv_path) {
            // Vérifiez si le fichier existe dans le répertoire 'public' où il est stocké
            if (Storage::disk('public')->exists($user->cv_path)) {
                // Supprimer le fichier existant
                Storage::disk('public')->delete($user->cv_path);
            }
        }
    
        // Vérification de l'existence du fichier
        if ($request->file('cv')) {
            // Générer un nom de fichier unique
            $file = $request->file('cv');
            
            // Créer un nom personnalisé pour le fichier : cv_{user_id}_{timestamp}.pdf
            $filename = 'cv_' . $user->id . '_' . Carbon::now()->timestamp . '.pdf';
    
            // Sauvegarder le fichier dans le répertoire 'cvs' avec le nom personnalisé
            $path = $file->storeAs('cvs', $filename, 'public');
    
            // Mettre à jour le profil de l'utilisateur avec le chemin du fichier
            $user->cv_path = $path;
            $user->save();
    
            // Retourner la réponse avec le lien vers le fichier
            return response()->json([
                'message' => 'CV uploaded successfully!',
                'cvUrl' => Storage::url($path)
            ], 200);
        }
    
        return response()->json(['error' => 'File upload failed.'], 500);
    }

    public function getCv($user_id)
    {
        $user = User::findOrFail($user_id);
        // Supposons que le chemin du CV est stocké dans un champ `cv_path` dans la table `users`
        if ($user->cv_path) {
            return response()->json([
                'cvUrl' => Storage::url($user->cv_path)
            ]);
        } else {
            // Retourner un tableau vide ou un objet avec la clé 'cvUrl' à null
            return response()->json([
                'cvUrl' => null
            ]);
        }
    }

    public function uploadProfilePicture(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Valider le fichier image
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Vérifier s'il y a déjà une image de profil et la supprimer avant d'enregistrer la nouvelle
        if ($user->picture) {
            // Vérifiez si le fichier existe dans le répertoire 'public' où il est stocké
            if (Storage::disk('public')->exists($user->picture)) {
                // Supprimer le fichier existant
                Storage::disk('public')->delete($user->picture);
            }
        }

        // Télécharger la nouvelle image
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Mettre à jour le champ 'picture' dans la base de données
        $user->picture = $path;
        $user->save();

        // Retourner l'URL de la nouvelle image
        return response()->json([
            'profilePictureUrl' => Storage::url($path),
        ]);
    }

    public function getProfilePicture($id)
    {
        $user = User::findOrFail($id);
        if ($user->picture) {
            return response()->json(['profilePictureUrl' => asset("storage/{$user->picture}")]);
        }
        return response()->json(['profilePictureUrl' => null]);
    }

    public function deleteCv($id)
    {
        // Vérifiez si l'utilisateur connecté correspond à l'utilisateur ciblé
        if (auth()->id() !== (int)$id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Récupérer l'utilisateur
        $user = User::findOrFail($id);

        // Vérifier si l'utilisateur a un chemin de CV dans la base de données
        if ($user->cv_path) {
            // Vérifiez si le fichier existe dans le répertoire 'public' où il est stocké
            if (Storage::disk('public')->exists($user->cv_path)) {
                // Supprimer le fichier
                Storage::disk('public')->delete($user->cv_path);
                
                // Mettre à jour la colonne cv_path à null dans la base de données
                $user->cv_path = null;
                $user->save();

                // Retourner une réponse indiquant que le CV a été supprimé
                return response()->json(['message' => 'CV deleted successfully']);
            } else {
                // Retourner une réponse si le fichier n'existe pas
                return response()->json(['error' => 'CV file not found'], 404);
            }
        }

        return response()->json(['error' => 'CV not found'], 404);
    }

    public function getSkills($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->skills); // Suppose que `skills` est une relation définie
    }

    public function addSkill(Request $request, User $user)
    {
        // Valider l'entrée
        $request->validate([
            'skill_id' => 'required|exists:skills,id',
        ]);

        // Ajouter la compétence à l'utilisateur
        $user->skills()->attach($request->skill_id);

        return response()->json(['message' => 'Skill added successfully']);
    }

    public function removeSkill(Request $request, User $user)
    {
        // Récupérer l'ID de la compétence à supprimer
        $skillId = $request->input('skill_id');

        // Supprimer la compétence de l'utilisateur (supposons une relation many-to-many)
        $user->skills()->detach($skillId);

        // Retourner une réponse indiquant que la compétence a été supprimée
        return response()->json(['message' => 'Skill removed successfully!']);
    }

}
