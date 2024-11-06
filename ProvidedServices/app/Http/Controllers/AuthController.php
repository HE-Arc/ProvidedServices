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
        // Validation des données
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found. Please check your email address and try again.'], 401);
        }

        // Combiner le mot de passe fourni avec le salt, puis vérifier avec le hash stocké
        if (!Hash::check($credentials['password'] . $user->salt, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials. Please check your password and try again.'], 401);
        }

        // Si les informations d'identification sont correctes
        Auth::login($user);

        return response()->json(['message' => 'Login successful', 'user' => $user], 200);
    }

    public function register(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string', // 'M' ou 'F'
            'role' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Générer un salt unique
        $salt = Str::random(16);

        // Hacher le mot de passe avec le salt
        $hashedPassword = Hash::make($request->password . $salt);

        // Création de l'utilisateur
        $user = User::create([
            'email' => $request->email,
            'password_hash' => $hashedPassword,
            'salt' => $salt,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'genre' => $request->gender,
            'role' => $request->role,
        ]);

        return response()->json(['message' => 'Registration successful', 'user' => $user], 201);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Déconnexion de l'utilisateur

        return response()->json(['message' => 'Logout successful'], 200);
    }
}
