<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skills;


class SkillsController extends Controller
{
    public function index()
    {
        $skills = Skills::all();
        return response()->json($skills);
    }
}
