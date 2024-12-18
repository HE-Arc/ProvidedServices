<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function show($id)
    {
        if (Auth::check()) {
            $user = User::findOrFail($id);
            return view('profile', ['user' => $user, 'authUserId' => Auth::id()]);
        }

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

        $request->validate([
            'cv' => 'required|mimes:pdf|max:2048',
        ]);

        if ($user->cv_path && Storage::disk('public')->exists($user->cv_path)) {
            Storage::disk('public')->delete($user->cv_path);
        }

        $file = $request->file('cv');
        $filename = 'cv_' . $user->id . '_' . Carbon::now()->timestamp . '.pdf';
        $path = $file->storeAs('cvs', $filename, 'public');

        $user->cv_path = $path;
        $user->save();

        return response()->json([
            'message' => 'CV téléchargé avec succès.',
            'cvUrl' => Storage::url($path)
        ], 200);
    }

    public function showCv($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'cvUrl' => $user->cv_path ? Storage::url($user->cv_path) : null,
        ]);
    }

    public function uploadProfilePicture(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($user->picture && Storage::disk('public')->exists($user->picture)) {
            Storage::disk('public')->delete($user->picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->picture = $path;
        $user->save();

        return response()->json([
            'profilePictureUrl' => Storage::url($path),
            'message' => 'Photo de profil mise à jour avec succès.',
        ]);
    }

    public function profilePicture($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'profilePictureUrl' => $user->picture ? asset("storage/{$user->picture}") : null,
        ]);
    }

    public function deleteCv($id)
    {
        if (auth()->id() !== (int)$id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->cv_path && Storage::disk('public')->exists($user->cv_path)) {
            Storage::disk('public')->delete($user->cv_path);
            $user->cv_path = null;
            $user->save();

            return response()->json(['message' => 'CV supprimé avec succès.']);
        }

        return response()->json(['error' => 'CV introuvable.'], 404);
    }

    public function skills($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->skills);
    }

    public function addSkill(Request $request, $id)
    {
        $request->validate([
            'skill_id' => 'required|exists:skills,id',
        ]);

        $user = User::findOrFail($id);

        $user->skills()->attach($request->skill_id);

        return response()->json(['message' => 'Compétence ajoutée avec succès.']);
    }

    public function removeSkill(Request $request, User $user)
    {
        $skillId = $request->input('skill_id');

        $user->skills()->detach($skillId);

        return response()->json(['message' => 'Compétence supprimée avec succès.']);
    }
}
