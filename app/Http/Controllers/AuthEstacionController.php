<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthEstacionController extends Controller
{
    public function mostrarLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'codigo' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('estacion')->attempt($credenciales)) {
            $request->session()->regenerate();
            return redirect()->intended('/despacho');
        }

        return back()->withErrors([
            'codigo' => 'El código o la contraseña no son correctos.',
        ])->onlyInput('codigo');
    }

    public function logout(Request $request)
    {
        Auth::guard('estacion')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}