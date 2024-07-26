<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'username' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],

            'team_id' => ['nullable', 'integer'],
            'country' => ['nullable', 'string', 'max:50'],
            'agreement' => ['accepted']
        ]);
        $attributes['password'] = bcrypt($attributes['password'] );
        $attributes['role'] = 'admin';


        session()->flash('success', 'Your account has been created.');
        $user = User::create($attributes);
        Auth::login($user);
        return redirect('/dashboard');
    }
}
