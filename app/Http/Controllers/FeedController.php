<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Preference;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedController extends Controller
{
    public function search(Request $request)
{
    Log::info("Search method called.");

    $query     = $request->query('query');
    $tipologia = $request->query('tipologia');
    $anno      = $request->query('anno');
    $genere    = $request->query('genere');
    $userId    = $request->query('user_id');

    $contentsQuery = Content::withCount('likes');

    if ($query || $tipologia || $anno || $genere) {

        if ($query) {
            $contentsQuery->where(function($q) use ($query) {
                $q->where('titolo', 'like', "%{$query}%")
                  ->orWhere('descrizione', 'like', "%{$query}%"); // ✅ era 'trama'
            });
        }

        if ($tipologia) {
            $contentsQuery->where('categoria', $tipologia);
        }

        if ($anno) {
            $contentsQuery->where('anno', $anno);
        }

        if ($genere) {
            $contentsQuery->whereHas('tags', function ($q) use ($genere) {
                $q->where('tags.id', $genere);
            });
        }

        $count = $contentsQuery->count();

        if ($count === 0) {
            return response()->json([
                'error' => 'Nessun contenuto trovato'
            ], 404);
        }

        $offset  = rand(0, max(0, $count - 1));
        $content = $contentsQuery->skip($offset)->first();

    } else {
        $content = $contentsQuery->inRandomOrder()->first();

        if (!$content) {
            return response()->json([
                'error' => 'Nessun contenuto disponibile'
            ], 404);
        }
    }

    // ✅ FIX - usa 'poster' invece di 'image'
    if (empty($content->poster)) {
        $posterUrl = $this->fetchWikipediaImage($content->titolo);
        if ($posterUrl) {
            $content->poster = $posterUrl;
            $content->save();
        }
    }

    $userHasLiked = false;
    if ($userId) {
        $userHasLiked = \App\Models\Preference::where('content_id', $content->id)
            ->where('user_id', $userId)
            ->where('liked', true)
            ->exists();
    }

    $WikipediaPlot = $this->fetchWikipediaPlot($content->titolo);

    return response()->json([
        'id'             => $content->id,
        'titolo'         => $content->titolo,
        'trama'          => $WikipediaPlot ?? $content->descrizione, // ✅ fallback su descrizione
        'image'          => $content->poster, // ✅ era $content->image
        'likes_count'    => $content->likes_count,
        'user_has_liked' => $userHasLiked,
    ]);
}
    public function getTags()
    {
        $tags = Tag::select('id', 'name')->get();
        return response()->json($tags);
    }

    private function fetchWikipediaImage($title)
    {
        $variants = [
            "$title (film)",
            "$title (movie)",
            $title
        ];

        foreach ($variants as $variant) {
            $image = $this->getImageFromWikipediaSummary($variant);
            if ($image) {
                Log::info("Fetched image for '$title' from variant '$variant': $image");
                return $image;
            }
        }

        $searchedTitle = $this->searchWikipediaTitle($title);
        if ($searchedTitle) {
            $image = $this->getImageFromWikipediaSummary($searchedTitle);
            if ($image) {
                Log::info("Fetched image for '$title' via search: $image");
                return $image;
            }
        }

        Log::warning("No image found for: $title");
        return null;
    }

    private function getImageFromWikipediaSummary($title)
    {
        $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);

        // ✅ FIX BUG 4 - timeout aggiunto
        $response = Http::timeout(3)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['thumbnail']['source'] ?? null;
        }

        return null;
    }

    private function searchWikipediaTitle($query)
    {
        $response = Http::timeout(3)->get("https://en.wikipedia.org/w/api.php", [
            'action'   => 'query',
            'list'     => 'search',
            'srsearch' => $query,
            'format'   => 'json'
        ]);

        if ($response->successful()) {
            return $response->json()['query']['search'][0]['title'] ?? null;
        }

        return null;
    }

    private function fetchWikipediaPlot($title)
    {
        $variants = [
            "$title (film)",
            "$title (movie)",
            $title,
        ];

        foreach ($variants as $variant) {
            // ✅ FIX BUG 4 - timeout aggiunto
            $response = Http::timeout(3)->get('https://en.wikipedia.org/w/api.php', [
                'action'      => 'query',
                'prop'        => 'extracts',
                'format'      => 'json',
                'exintro'     => true,
                'explaintext' => true,
                'redirects'   => true,
                'titles'      => $variant,
            ]);

            if ($response->successful()) {
                $pages = $response->json()['query']['pages'];
                foreach ($pages as $page) {
                    if (!empty($page['extract'])) return $page['extract'];
                }
            }
        }

        return null;
    }
}