<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class PostCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->comment,
        ]);

        return back()->with('success', 'Commento pubblicato!');
    }

    public function destroy(Comment $comment)
{
    $user = Auth::user();

    if ($user->id === $comment->user_id || $user->isAdmin()) {
        $comment->delete();
        return back()->with('success', 'Commento eliminato.'); // ✅ redirect, non JSON
    }

    abort(403, 'Non autorizzato');
}
}