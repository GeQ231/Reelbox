<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class CommentController extends Controller
{
    public function index($id)
    {
        $comments = Comment::where('content_id', $id)
                    ->with('user:id,name')
                    ->orderBy('created_at', 'desc')
                    ->get();
    
        Log::info('Caricati commenti per content_id='.$id, $comments->map(function($c) {
            return [
                'comment_id' => $c->id,
                'user_id' => $c->user_id,
                'user_name' => $c->user->name ?? 'Anonimo',
                'body' => $c->body,
            ];
        })->toArray());
    
        return $comments->map(function ($comment) {
            return [
                'body' => $comment->body,  
                'user_name' => $comment->user->name ?? 'Anonimo',
            ];
        });
    }

    //funzione per il salvataggio di un commento nel db
    public function store(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:1000']);  

        //insert dell commento nella table
        Comment::create([
            'user_id' => auth()->id(),
            'content_id' => $id,
            'body' => $request->body,  
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Comment $comment)
    {
        $user = auth()->user();

        if ($user->id === $comment->user_id || $user->isAdmin()) {
            $comment->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Non autorizzato'], 403);
    }



}
