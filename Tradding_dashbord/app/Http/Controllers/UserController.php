<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;



class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->paginate(7);

        $teams = Team::all();
        return view('management-tables.user-management', compact('users', 'teams'));
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = User::with('team:id,team_name', 'league:id,league_name') // Charger la relation league
    //             ->where('role', 'user')
    //             ->latest()
    //             ->get();

    //         return datatables()->of($data)
    //             ->addColumn('team', function ($row) {
    //                 return $row->team ? $row->team->team_name : 'Aucune';
    //             })
    //             ->addColumn('league_name', function ($row) {
    //                 return $row->league ? $row->league->league_name : 'Aucune'; // Ajouter la colonne league_name
    //             })
    //             ->addColumn('actions', function ($row) {
    //                 return '<button class="btn btn-primary btn-sm editUserButton"
    //                            data-id="' . $row->id . '"
    //                            data-username="' . $row->username . '"
    //                            data-email="' . $row->email . '"
    //                            data-team-id="' . $row->team_id . '">  <i class="fa fa-edit fs-6"></i></button>
    //                         <button href="#" class="btn btn-danger btn-sm deleteUserButton "
    //                            data-id="' . $row->id . '"
    //                            data-username="' . $row->username . '"> <i class="fa fa-trash fs-6"></i></button>';
    //             })
    //             ->rawColumns(['actions'])
    //             ->make(true);
    //     }

    //     // Chargement initial de la vue avec la liste des équipes
    //     $teams = Team::select('id', 'team_name')->get();
    //     return view('management-tables.user-management', compact('teams'));
    // }



    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', 'unique:users,email'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'L\'adresse email est déjà utilisée.',
            'password.confirmed' => 'Le mot de passe et sa confirmation ne correspondent pas.',

        ]);


        // Création de l'utilisateur avec le rôle par défaut 'user'
        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'country' => null, // Always set country to null
            'team_id' => $request->team_id,
            'password' => bcrypt($request->password),
            'role' => 'user', // Rôle par défaut
        ]);

        return redirect()->route('users.index')->with('message_success', 'Utilisateur ajouté avec succès.');
    }


    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate(
            [
                'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
                'email' => [
                    'sometimes',
                    'required',
                    'string',
                    'email',
                    'max:255',
                ],

                'password' => 'nullable|string|min:5|confirmed',
                'team_id' => 'nullable|exists:teams,id',
            ],
            [
                'email.unique' => 'L\'adresse email est déjà utilisée.',
                'password.confirmed' => 'Le mot de passe et sa confirmation ne correspondent pas.',

            ]
        );

        $data = [
            'username' => $request->username,
            'email' => $request->email,
            'country' => null, // Toujours définir country à null si nécessaire
        ];

        if ($request->filled('team_id')) {
            $data['team_id'] = $request->team_id;
        }

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Vérifier l'unicité de l'email avant la mise à jour


        $user->update($data);

        return redirect()->route('users.index')->with('message_success', 'Utilisateur mis à jour avec succès.');
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('message_success', 'Utilisateur supprimé avec succès.');
    }
}
