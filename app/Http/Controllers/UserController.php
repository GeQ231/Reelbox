<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Tag;

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
            'preferences' => $user->preferences,
        ]);
    }

    // ✅ AGGIUNTO - mancava!
    public function showPreferences()
    {
        $user = Auth::user()->load('preferences');
        $tags = Tag::all();

        return view('profile.preferences', [
            'user' => $user,
            'tags' => $tags,
            'preferences' => $user->preferences,
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferenze' => 'nullable|array',
            'preferenze.*' => 'exists:tags,id',
        ]);

        $user = Auth::user();
        $user->preferences()->sync($request->input('preferenze', []));

        return redirect()->route('preferences')->with('success', 'Preferenze aggiornate');
    }

    // ✅ AGGIUNTO - mancava!
    public function adminUsers()
    {
        $users = User::paginate(20);
        return view('admin.users', compact('users'));
    }

    // ✅ AGGIUNTO - mancava!
    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utente eliminato.');
    }

    public function showRegistrationForm() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
    
        Auth::login($user);
        return redirect('/');
    }
    
    public function showLoginForm()
    { 
        return view('auth.login');
    }

    public function login(Request $request)
    {
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}