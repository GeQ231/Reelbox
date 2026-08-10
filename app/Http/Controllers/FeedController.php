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

        Log::info("Search parameters: ", $request->all());

        $contentsQuery = Content::withCount('likes');

        if ($query || $tipologia || $anno || $genere) {
            Log::info("Applying filters to Content query...");

            // ✅ FIX BUG 7 - orWhere raggruppato correttamente
            if ($query) {
                $contentsQuery->where(function($q) use ($query) {
                    $q->where('titolo', 'like', "%{$query}%")
                      ->orWhere('trama', 'like', "%{$query}%");
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
            Log::info("Filtered content count: $count");

            if ($count === 0) {
                return response()->json([
                    'error' => 'Nessun contenuto corrisponde ai criteri di ricerca'
                ], 404);
            }

            $offset  = rand(0, max(0, $count - 1));
            $content = $contentsQuery->skip($offset)->first();
            Log::info("Random content selected: " . ($content->titolo ?? 'none'));

        } else {
            // Nessun filtro: contenuto casuale
            $content = $contentsQuery->inRandomOrder()->first();

            if (!$content) {
                return response()->json([
                    'error' => 'Nessun contenuto disponibile'
                ], 404);
            }
        }

        // Fetch immagine Wikipedia se mancante
        if (empty($content->image)) {
            $imageUrl = $this->fetchWikipediaImage($content->titolo);
            if ($imageUrl) {
                $content->image = $imageUrl;
                $content->save();
                Log::info("Fetched and saved image for {$content->titolo}: {$imageUrl}");
            } else {
                Log::warning("No image found for: {$content->titolo}");
            }
        }

        // ✅ FIX BUG 6 - user_has_liked usa Preference correttamente
        $userHasLiked = false;
        if ($userId) {
            $userHasLiked = Preference::where('content_id', $content->id)
                ->where('user_id', $userId)
                ->where('liked', true)
                ->exists();
        }

        // Fetch trama Wikipedia
        $WikipediaPlot = $this->fetchWikipediaPlot($content->titolo);

        return response()->json([
            'id'             => $content->id,
            'titolo'         => $content->titolo,
            'trama'          => $WikipediaPlot,
            'image'          => $content->image,
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