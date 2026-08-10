<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\Preference; // Aggiungi questa riga

class FavoriteController extends Controller
{
    public function toggle($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Devi essere loggato.'], 401);
        }

        $content = Content::findOrFail($id);

        // Cerchiamo nella tabella 'preferences'
        // Assumiamo che tu abbia una colonna 'is_favorite' o usiamo 'liked' come flag
        $preference = Preference::where('user_id', $user->id)
            ->where('content_id', $id)
            ->first();

        if ($preference) {
            $preference->delete();
            $favorited = false;
        } else {
            Preference::create([
                'user_id' => $user->id,
                'content_id' => $id,
                'liked' => true, // O un flag specifico per i preferiti
            ]);
            $favorited = true;
        }

        return response()->json([
            'favorited' => $favorited
        ]);
    }
}