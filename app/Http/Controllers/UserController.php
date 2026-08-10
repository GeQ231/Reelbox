<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//per debug
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{

    public function profile()
    {
        $user = Auth::user()->load('preferences');
    
        Log::info('Preferenze caricate:', [
            'user_id' => $user->id,
            'preferences' => $user->preferences->pluck('name')->toArray()
        ]);
    
        return view('profile.profile', [
            'user' => $user,
            'preferences' => $user->preferences, // collection di Tag
        ]);
    }
    





    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferenze' => 'nullable|array',
            'preferenze.*' => 'exists:tags,id',
        ]);

        $user = Auth::user();

        // sincronizza le preferenze usando la tabella pivot
        $user->preferences()->sync($request->input('preferenze', []));

        return redirect()->route('preferences')->with('success', 'Preferenze aggiornate');
    }



    //------F u n z i o n i   p e r   c r e a z i o n e    p r o f i l o    e    l o g  i n 

    public function showRegistrationForm() {
    //questa funzione genera la pagina per la registrazione di un nuovo utente
        return view('auth.register');
    }

    
    public function register(Request $request) {
    //questa funzione permette di registrare l'utente (inserisce il nuovo user nel db)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
    
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Non bcrypt perché Laravel lo fa in automatico
        ]);
    
        Auth::login($user);
        //faccio il redirect alla homepage appena dopo la registrazione
        return redirect('/');
    }
    
    
    
    public function showLoginForm()
    { 
        //questa funzione mostra il form del log in 
        return view('auth.login');
    }

    public function login(Request $request)
    {
        //questa funzione effettua il log in 
        $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
        'email' => 'Credenziali non valide.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        //questa funzione effettua il logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }




}

