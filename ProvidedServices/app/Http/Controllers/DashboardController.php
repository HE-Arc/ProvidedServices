<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Affiche la page du tableau de bord.
     */
    public function index()
    {
        return view('dashboard');
    }
}
