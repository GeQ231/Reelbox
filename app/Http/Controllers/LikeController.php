<?php

namespace App\Http\Controllers;

use App\Models\Preference; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function countLikes($contentId)
    {
        $count = Preference::where('content_id', $contentId)->where('liked', true)->count();
        return response()->json(['likes' => $count]);
    }

    public function toggle($contentId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Utente non autenticato'], 401);
            }
    
            $existingPreference = Preference::where('user_id', $user->id)
                                        ->where('content_id', $contentId)
                                        ->first();
    
            $content = Content::with('tags')->find($contentId);
            if (!$content) {
                return response()->json(['error' => 'Contenuto non trovato'], 404);
            }
    
            $tagIds = $content->tags->pluck('id')->toArray();
    
            if ($existingPreference) {
                // Rimuovi o imposta liked a false
                $existingPreference->delete();
                $userHasLiked = false;
    
                foreach ($tagIds as $tagId) {
                    $otherLikes = Preference::where('user_id', $user->id)
                        ->where('liked', true)
                        ->whereHas('content.tags', function ($query) use ($tagId) {
                            $query->where('tags.id', $tagId);
                        })
                        ->count();
    
                    if ($otherLikes === 0) {
                        DB::table('users_tags_preferences')
                            ->where('user_id', $user->id)
                            ->where('tag_id', $tagId)
                            ->delete();
                    }
                }
            } else {
                // Aggiungi la preferenza / like
                Preference::create([
                    'user_id' => $user->id,
                    'content_id' => $contentId,
                    'liked' => true,
                ]);
    
                $userHasLiked = true;
    
                foreach ($tagIds as $tagId) {
                    $exists = DB::table('users_tags_preferences')
                        ->where('user_id', $user->id)
                        ->where('tag_id', $tagId)
                        ->exists();
    
                    if (!$exists) {
                        DB::table('users_tags_preferences')->insert([
                            'user_id' => $user->id,
                            'tag_id' => $tagId,
                        ]);
                    }
                }
            }
    
            $likeCount = Preference::where('content_id', $contentId)->where('liked', true)->count();
    
            return response()->json([
                'like_count' => $likeCount,
                'user_has_liked' => $userHasLiked,
            ]);
        } catch (\Exception $e) {
            Log::error('Errore interno in LikeController@toggle', ['exception' => $e]);
            return response()->json(['error' => 'Errore interno: ' . $e->getMessage()], 500);
        }
    }
}