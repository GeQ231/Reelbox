<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Post $post)
    {
        $user = Auth::user();

        // ✅ Controlla se il like esiste
        $exists = DB::table('like_post')
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->exists();

        if ($exists) {
            // ✅ Rimuovi il like con DB diretto
            DB::table('like_post')
                ->where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->delete();
            $userHasLiked = false;
        } else {
            // ✅ Aggiungi il like
            DB::table('like_post')->insert([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $userHasLiked = true;
        }

        $likesCount = DB::table('like_post')
            ->where('post_id', $post->id)
            ->count();

        return response()->json([
            'likes_count'    => $likesCount,
            'user_has_liked' => $userHasLiked,
        ]);
    }
}