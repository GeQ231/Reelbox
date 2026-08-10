<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\LikePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostLikeController extends Controller
{
    public function __construct()
    {
        // Assicuriamoci che solo utenti autenticati possano mettere like
        $this->middleware('auth');
    }

    /**
     * Toggle like: aggiunge o rimuove il like a seconda se esiste già
     */
    public function toggle(Post $post)
    {
            $user = Auth::user();

            // Cerca se esiste già il like di questo user su questo post
            $like = LikePost::where('user_id', $user->id)
                            ->where('post_id', $post->id)
                            ->first();

            if ($like) {
                // Se esiste, lo rimuove
                $like->delete();
                $userHasLiked = false;
            } else {
                // Se non esiste, crea un nuovo like
                LikePost::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $userHasLiked = true;
            }

            // Conta i like aggiornati
            $likesCount = $post->likes()->count();

            return response()->json([
                'likes_count' => $likesCount,
                'user_has_liked' => $userHasLiked,
            ]);
        } 
    }

