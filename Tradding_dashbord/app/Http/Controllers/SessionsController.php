<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;


// class SessionsController extends Controller
// {
//     public function create()
//     {
//         return view('session.login-session');
//     }

//     public function store()
//     {
//         $attributes = request()->validate([
//             'email' => 'required|email',
//             'password' => 'required'
//         ]);

//         if (Auth::attempt($attributes)) {
//             session()->regenerate();
//             return redirect('dashboard')->with(['success' => 'You are logged in.']);
//         } else {

//             return back()->withErrors(['email' => 'Email or password invalid.']);
//         }
//     }


//     public function destroy(Request $request)
//     {
//         Auth::logout();
//         $request->session()->invalidate();
//         $request->session()->regenerateToken();

//         return redirect('/login');
//     }

// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($attributes)) {
            session()->regenerate();

            // Vérifier si l'utilisateur a le rôle 'admin'
            if (Auth::user()->role === 'admin') {
                return redirect('dashboard')->with(['success' => 'Vous êtes connecté.']);
            } else {
                Auth::logout();
                return redirect('/login')->withErrors(['email' => 'Accès refusé. Seuls les administrateurs peuvent accéder au tableau de bord.']);
            }
        } else {
            return back()->withErrors(['email' => 'Email ou mot de passe invalide.']);
        }
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
