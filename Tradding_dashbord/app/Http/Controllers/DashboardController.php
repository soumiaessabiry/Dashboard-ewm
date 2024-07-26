<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\League;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $userCount = User::count(); // Récupère le nombre total d'utilisateurs
        $leagueCount = League::count(); // Récupère le nombre total d'utilisateurs
        $leaguedashbord = League::All(); // Récupère le nombre total d'utilisateurs
        $teamCount = Team::count(); // Récupère le nombre total d'utilisateurs
        return view('dashboard', compact('userCount','leagueCount','teamCount','leaguedashbord'));
    }
}
