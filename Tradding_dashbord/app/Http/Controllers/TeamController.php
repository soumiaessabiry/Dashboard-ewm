<?php

// namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Team;
// use App\Models\League;
// use App\Models\User;

// class TeamController extends Controller
// {
//     public function index()
//     {

//         $teams = Team::with('users')->paginate(7); // Utilisez paginate() au lieu de get()
//         $leagues = League::all();
//         return view('management-tables.teams-management', compact('teams', 'leagues'));
//     }

//     public function store(Request $request)
//     {
//         $validatedData = $request->validate([
//             'league_id' => 'required|exists:leagues,id',
//             'team_code' => 'required|string|max:255',
//             'team_name' => 'required|string|max:255',
//         ]);

//         Team::create($validatedData);

//         return redirect()->route('teams.index')->with('Team_success', 'Team created successfully.');
//     }
//     public function destroy(Team $team)
//     {
//         $team->delete();
//         return redirect()->route('teams.index')->with('Team_success', 'Team deleted successfully.');
//     }
//     public function update(Request $request, Team $team)
// {
//     $validatedData = $request->validate([
//         'league_id' => 'required|exists:leagues,id',
//         'team_code' => 'required|string|max:255',
//         'team_name' => 'required|string|max:255',
//     ]);

//     $team->update($validatedData);

//     return redirect()->route('teams.index')->with('Team_success', 'Team updated successfully.');
// }


//     // equips
//     public function storeMember(Request $request, $team_id)
//     {
//         $validatedData = $request->validate([
//             'username' => 'required|string|max:255',
//             'email' => 'required|string|email|max:255|unique:users',
//             'password' => 'required|string|min:8|confirmed',
//             'country' => 'required|string|max:255',
//         ]);

//         $validatedData['team_id'] = $team_id;
//         $validatedData['role'] = 'user';
//         $validatedData['password'] = bcrypt($validatedData['password']);

//         User::create($validatedData);

//         return redirect()->route('teams.index')->with('Team_success', 'Member added successfully.');
//     }
//     public function showMembers(Team $team)
//     {
//         $members = $team->users;
//         return view('management-tables.team-members', compact('team', 'members'));
//     }
//     public function destroyMember(Team $team, User $user)
//     {
//         $user->delete();
//         return redirect()->route('teams.index')->with('Team_success', 'Member deleted successfully.');
//     }
// }
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\League;
use App\Models\User;
use Yajra\DataTables\DataTables;

class TeamController extends Controller
{
    // public function index()
    // {
    //     $teams = Team::with('users')->paginate(7);
    //     $leagues = League::all();
    //     return view('management-tables.teams-management', compact('teams', 'leagues'));
    // }
    public function index()
{
    $teams = Team::with('users')->paginate(7);
    $leagues = League::all();
    return view('management-tables.teams-management', compact('teams', 'leagues'));
}

// public function index(Request $request)
// {
//     if ($request->ajax()) {
//         $data = Team::with('users')->latest()->get();
//         return DataTables::of($data)
//             ->addColumn('action', function ($team) {
//                 $button = '<a href="' . route('teams.showMembers', $team->id) . '" class="btn btn-info btn-sm">Voir les membres</a>';
//                 $button .= ' <a href="' . route('teams.update', $team->id) . '" class="btn btn-primary btn-sm">Éditer</a>';
//                 $button .= ' <button type="button" data-toggle="modal" data-target="#deleteModal' . $team->id . '" class="btn btn-danger btn-sm">Supprimer</button>';
//                 return $button;
//             })
//             ->rawColumns(['action'])
//             ->make(true);
//     }

//     $leagues = League::all();
//     return view('management-tables.teams-management', compact('leagues'));
// }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'league_id' => 'required|exists:leagues,id',
            'team_code' => 'required|string|max:255|unique:teams',
            'team_name' => 'required|string|max:255|unique:teams',
        ], [
            'team_code.unique' => 'Le code d\'équipe  existe déjà.',
            'team_name.unique' => 'Le nom d\'équipe  existe déjà.',
        ]);

        Team::create($validatedData);

        return redirect()->route('teams.index')->with('team_success', 'Équipe créée avec succès.');
    }


    public function update(Request $request, Team $team)
    {
        $validatedData = $request->validate([
            'league_id' => 'required|exists:leagues,id',
            'team_code' => 'required|string|max:255|unique:teams,team_code,' . $team->id,
            'team_name' => 'required|string|max:255|unique:teams,team_name,' . $team->id,
        ], [
            'team_code.unique' => 'Le code d\'équipe  existe déjà.',
            'team_name.unique' => 'Le nom d\'équipe  existe déjà.',
        ]);

        $team->update($validatedData);

        return redirect()->route('teams.index')->with('team_success', 'Équipe mise à jour avec succès.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return redirect()->route('teams.index')->with('team_success', 'Équipe supprimée avec succès.');
    }

    public function storeMember(Request $request, $team_id)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'required|string|max:255',
        ], [
            'username.unique' => 'Le nom d\'utilisateur spécifié existe déjà.',
            'email.unique' => 'L\'adresse email spécifiée existe déjà.',
        ]);

        $validatedData['team_id'] = $team_id;
        $validatedData['role'] = 'user';
        $validatedData['password'] = bcrypt($validatedData['password']);

        User::create($validatedData);

        return redirect()->route('teams.index')->with('team_success', 'Membre ajouté avec succès.');
    }

    public function showMembers(Team $team)
    {
        $members = $team->users;
        return view('management-tables.team-members', compact('team', 'members'));
    }

    public function destroyMember(Team $team, User $user)
    {
        $user->delete();
        return redirect()->route('teams.index')->with('team_success', 'Membre supprimé avec succès.');
    }
}
