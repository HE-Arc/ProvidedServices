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
            // Retournez la vue avec les données de l'utilisateur
            return view('profile', ['user' => Auth::user()]); // Assurez-vous que vous avez une vue profile.blade.php
        }

        // Si non authentifié, redirigez vers la page de connexion
        return redirect()->route('login');
    }

    public function update(Request $request, $id) {
        $user = User::find($id);
        
        if ($user) {
            // Validation des données envoyées
            $validatedData = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'genre' => 'required|string',
                'role' => 'required|string',
            ]);
            
            // Mise à jour des données utilisateur
            $user->update($validatedData);
            
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }    

    public function uploadCv(Request $request, $id)
    {
        // Validation du fichier
        $request->validate([
            'cv' => 'required|mimes:pdf|max:2048' // Limité à 2 MB pour les PDF
        ]);
    
        // Vérification de l'existence du fichier
        if ($request->file('cv')) {
            // Générer un nom de fichier unique
            $file = $request->file('cv');
            $user = User::findOrFail($id);
            
            // Créer un nom personnalisé pour le fichier : cv_{user_id}_{timestamp}.pdf
            $filename = 'cv_' . $user->id . '_' . Carbon::now()->timestamp . '.pdf';
    
            // Sauvegarder le fichier dans le répertoire 'cvs' avec le nom personnalisé
            $path = $file->storeAs('cvs', $filename, 'public');
    
            // Mettre à jour le profil de l'utilisateur avec le chemin du fichier
            $user->description = $path;
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
        if ($user->description) {
            return response()->json([
                'cvUrl' => Storage::url($user->description)
            ]);
        } else {
            return response()->json([
                'message' => 'No CV found for this user.'
            ], 404);
        }
    }
}
