<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Validation\Rule;
// use App\Models\User;

// class InfoUserController extends Controller
// {
//     /**
//      * Affiche le formulaire de mise à jour du profil utilisateur.
//      *
//      * @return \Illuminate\View\View
//      */
//     public function edit()
//     {
//         $user = Auth::user();
//         return view('management-tables.user-profile', compact('user'));
//     }

//     /**
//      * Traite la soumission du formulaire de mise à jour du profil utilisateur.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\RedirectResponse
//      */
//     public function update(Request $request)
//     {
//         $user = Auth::user();

//         $attributes = $request->validate([
//             'username' => ['required', 'max:50'],
//             'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
//             'password' => ['nullable', 'min:6', 'confirmed'],
//         ]);

//         // Mise à jour des informations de l'utilisateur
//         $user->update([
//             'username' => $attributes['username'],
//             'email' => $attributes['email'],
//             'password' => bcrypt($attributes['password']),
//         ]);

//         return redirect()->route('user-profile.edit')->with('profile_success', 'Profil mis à jour avec succès');
//     }
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

class InfoUserController extends Controller
{
    /**
     * Affiche le formulaire de mise à jour du profil utilisateur.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('management-tables.user-profile', compact('user'));
    }

    /**
     * Traite la soumission du formulaire de mise à jour du profil utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $attributes = $request->validate([
            'username' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:6', 'confirmed'],
        ]);

        // Mise à jour des informations de l'utilisateur
        $user->username = $attributes['username'];
        $user->email = $attributes['email'];

        // Mise à jour du mot de passe seulement s'il est fourni
        if (!empty($attributes['password'])) {
            $user->password = bcrypt($attributes['password']);
        }

        $user->save();

        return redirect()->route('user-profile.edit')->with('profile_success', 'Profil mis à jour avec succès');
    }
}
