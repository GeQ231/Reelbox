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
                      ->orWhere('descrizione', 'like', "%{$query}%");
                });
            }
            if ($tipologia) $contentsQuery->where('categoria', $tipologia);
            if ($anno) $contentsQuery->where('anno', $anno);
            if ($genere) {
                $contentsQuery->whereHas('tags', function ($q) use ($genere) {
                    $q->where('tags.id', $genere);
                });
            }

            $count = $contentsQuery->count();
            if ($count === 0) {
                return response()->json(['error' => 'Nessun contenuto trovato'], 404);
            }

            $offset  = rand(0, max(0, $count - 1));
            $content = $contentsQuery->skip($offset)->first();
        } else {
            $content = $contentsQuery->inRandomOrder()->first();
            if (!$content) {
                return response()->json(['error' => 'Nessun contenuto disponibile'], 404);
            }
        }

        // ✅ TMDB con categoria
        try {
            if (empty($content->poster) || empty($content->descrizione)) {
                $tmdbData = $this->fetchTMDBData($content->titolo, $content->anno, $content->categoria);

                if (!empty($tmdbData['poster']) && empty($content->poster)) {
                    $content->poster = $tmdbData['poster'];
                }
                if (!empty($tmdbData['descrizione']) && empty($content->descrizione)) {
                    $content->descrizione = $tmdbData['descrizione'];
                }
                $content->save();
            }
        } catch (\Exception $e) {
            Log::error('Errore TMDB: ' . $e->getMessage());
        }

        // ✅ Wikipedia fallback
        try {
            if (empty($content->descrizione)) {
                $plot = $this->fetchWikipediaPlot($content->titolo);
                if ($plot) {
                    $content->descrizione = $plot;
                    $content->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Errore Wikipedia: ' . $e->getMessage());
        }

        // ✅ Wikipedia immagine fallback
        try {
            if (empty($content->poster)) {
                $poster = $this->fetchWikipediaImage($content->titolo);
                if ($poster) {
                    $content->poster = $poster;
                    $content->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Errore Wikipedia image: ' . $e->getMessage());
        }

        $userHasLiked = false;
        if ($userId) {
            $userHasLiked = Preference::where('content_id', $content->id)
                ->where('user_id', $userId)
                ->where('liked', true)
                ->exists();
        }

        return response()->json([
            'id'             => $content->id,
            'titolo'         => $content->titolo,
            'trama'          => $content->descrizione ?? 'Nessuna trama disponibile.',
            'image'          => $content->poster,
            'likes_count'    => $content->likes_count,
            'user_has_liked' => $userHasLiked,
        ]);
    }

    public function getTags()
    {
        $tags = Tag::select('id', 'name')->get();
        return response()->json($tags);
    }

    // ============ TMDB ============
    private function fetchTMDBData($titolo, $anno = null, $categoria = 'film')
    {
        $apiKey = env('TMDB_API_KEY');
        if (!$apiKey) return null;

        // ✅ Endpoint diverso per serie TV
        $endpoint = $categoria === 'serie_tv'
            ? 'https://api.themoviedb.org/3/search/tv'
            : 'https://api.themoviedb.org/3/search/movie';

        $response = Http::timeout(5)->get($endpoint, [
            'api_key'  => $apiKey,
            'query'    => $titolo,
            'language' => 'it-IT',
        ]);

        if ($response->successful()) {
            $result = $response->json()['results'][0] ?? null;
            if ($result) {
                $poster      = isset($result['poster_path'])
                    ? "https://image.tmdb.org/t/p/w500{$result['poster_path']}"
                    : null;
                $descrizione = !empty($result['overview']) ? $result['overview'] : null;

                // ✅ Fallback inglese
                if (empty($descrizione)) {
                    $responseEn = Http::timeout(5)->get($endpoint, [
                        'api_key'  => $apiKey,
                        'query'    => $titolo,
                        'language' => 'en-US',
                    ]);

                    if ($responseEn->successful()) {
                        $resultEn = $responseEn->json()['results'][0] ?? null;
                        if ($resultEn && !empty($resultEn['overview'])) {
                            $descrizione = $resultEn['overview'];
                        }
                    }
                }

                return [
                    'poster'      => $poster,
                    'descrizione' => $descrizione,
                ];
            }
        }

        return null;
    }

    // ============ WIKIPEDIA IMMAGINE ============
    private function fetchWikipediaImage($title)
    {
        $variants = ["$title (film)", "$title (TV series)", $title];

        foreach ($variants as $variant) {
            $image = $this->getImageFromWikipediaSummary($variant);
            if ($image) return $image;
        }

        $searchedTitle = $this->searchWikipediaTitle($title);
        if ($searchedTitle) {
            return $this->getImageFromWikipediaSummary($searchedTitle);
        }

        return null;
    }

    private function getImageFromWikipediaSummary($title)
    {
        $url      = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        $response = Http::timeout(3)->get($url);

        if ($response->successful()) {
            return $response->json()['thumbnail']['source'] ?? null;
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

    // ============ WIKIPEDIA TRAMA ============
    private function fetchWikipediaPlot($title)
    {
        $variants = ["$title (film)", "$title (TV series)", "$title (movie)", $title];

        foreach ($variants as $variant) {
            $response = Http::timeout(5)->get('https://it.wikipedia.org/w/api.php', [
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

        foreach ($variants as $variant) {
            $response = Http::timeout(5)->get('https://en.wikipedia.org/w/api.php', [
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