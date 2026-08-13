<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index($id)
    {
        $comments = Comment::where('content_id', $id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments->map(function ($comment) {
            return [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'user_name'  => $comment->user->name ?? 'Anonimo',
                'created_at' => $comment->created_at->diffForHumans(),
            ];
        }));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        Comment::create([
            'user_id'    => auth()->id(),
            'content_id' => $id,
            'body'       => $request->body,
        ]);

        // ✅ Se AJAX ritorna JSON, altrimenti redirect
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Commento aggiunto!');
    }

    public function destroy(Comment $comment)
    {
        $user = auth()->user();

        if ($user->id === $comment->user_id || $user->isAdmin()) {
            $comment->delete();

            // ✅ Se AJAX ritorna JSON, altrimenti redirect
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', 'Commento eliminato!');
        }

        return response()->json(['error' => 'Non autorizzato'], 403);
    }
}