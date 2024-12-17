<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => "Utilisateur non trouvé. Veuillez vérifier votre adresse e-mail et réessayer."], 401);
        }

        if (!Hash::check($credentials['password'] . $user->salt, $user->password_hash)) {
            return response()->json(['message' => "Identifiants invalides. Veuillez vérifier votre mot de passe et réessayer."], 401);
        }

        Auth::login($user);

        return response()->json(['message' => "Connexion réussie", 'user' => $user], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string',
            'role' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['erreurs' => $validator->errors()], 422);
        }

        $salt = Str::random(16);
        $hashedPassword = Hash::make($request->password . $salt);

        $user = User::create([
            'email' => $request->email,
            'password_hash' => $hashedPassword,
            'salt' => $salt,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'genre' => $request->gender,
            'role' => $request->role,
        ]);

        return response()->json(['message' => "Inscription réussie", 'user' => $user], 201);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json(['message' => "Déconnexion réussie"], 200);
    }
}
