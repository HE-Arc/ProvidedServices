<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Rediriger vers la page de login si l'utilisateur n'est pas authentifié
            return redirect()->route('login');
        }

        return $next($request);
    }
}
