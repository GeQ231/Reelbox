<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\CommentPost;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PostCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Salva un commento sul post
   public function store(Request $request, Post $post)
{
    $request->validate([
        'comment' => 'required|string|max:1000',
    ]);

    $user = Auth::user();

    // Verifica che l'utente sia autenticato e presente nel DB
    if (!$user || !$user->exists) {
        return back()->with('error', 'Utente non valido o autenticato');
    }

    // Ora puoi creare il commento
    $post->comments()->create([
        'user_id' => $user->id,
        'comment' => $request->comment,
    ]);

    return back()->with('success', 'Commento pubblicato!');
}



    // Cancella un commento (solo autore o admin, puoi personalizzare)
    public function destroy(CommentPost $comment)
    {
        $user = Auth::user();

        if ($user->id === $comment->user_id  || $user->isAdmin() ) {
            $comment->delete();
            return back()->with('success', 'Commento eliminato.');
        }

        abort(403, 'Non autorizzato');
    }
}
