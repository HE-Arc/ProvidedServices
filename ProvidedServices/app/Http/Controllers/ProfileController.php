<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    // Met à jour les informations de l'utilisateur
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); // Récupère l'utilisateur par ID

        // Valider les données entrantes
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id, // Vérifie l'unicité de l'email sauf pour l'utilisateur actuel
            // Ajoutez d'autres validations si nécessaire
        ]);

        // Met à jour les informations de l'utilisateur
        $user->update($validatedData);

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}
