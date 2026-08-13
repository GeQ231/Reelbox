<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $tipo  = $request->query('categoria');
        $query = Content::query();

        if ($tipo === 'film') {
            $query->films();
        } elseif ($tipo === 'tv') {
            $query->tvShows();
        }

        $contents = $query->paginate(10);
        return view('contents.index', compact('contents', 'tipo'));
    }

    public function show(Content $content)
    {
        Log::info('Accessed content show page', ['content_id' => $content->id]);
        $content = Content::with('tags', 'comments.user')->findOrFail($content->id);

        // ✅ TMDB - Poster, Descrizione e Regista
        try {
            if (empty($content->poster) || empty($content->descrizione) || empty($content->regista)) {
                $tmdbData = $this->fetchTMDBData($content->titolo, $content->anno, $content->categoria);

                if (!empty($tmdbData['poster']) && empty($content->poster)) {
                    $content->poster = $tmdbData['poster'];
                }
                if (!empty($tmdbData['descrizione']) && empty($content->descrizione)) {
                    $content->descrizione = $tmdbData['descrizione'];
                }
                if (!empty($tmdbData['regista']) && empty($content->regista)) {
                    $content->regista = $tmdbData['regista'];
                }
                $content->save();
            }
        } catch (\Exception $e) {
            Log::error('Errore TMDB: ' . $e->getMessage());
        }

        // ✅ Wikipedia - Fallback descrizione
        try {
            if (empty($content->descrizione)) {
                $plot = $this->fetchWikipediaPlot($content);
                if ($plot) {
                    $content->descrizione = $plot;
                    $content->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Errore Wikipedia: ' . $e->getMessage());
        }

        // ✅ YouTube - Trailer
        try {
            if (empty($content->trailer_url)) {
                $trailer = $this->findYouTubeTrailerLink($content->titolo);
                if ($trailer) {
                    $content->trailer_url = $trailer;
                    $content->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Errore YouTube: ' . $e->getMessage());
        }
            $isFavorited = false;
                if (auth()->check()) {
                    $isFavorited = \App\Models\Preference::where('user_id', auth()->id())
                        ->where('content_id', $content->id)
                        ->exists();
                }

    return view('films.show', compact('content', 'isFavorited'));
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Commento aggiunto!');
    }

    // ============ TMDB ============
    private function fetchTMDBData($titolo, $anno = null, $categoria = 'film')
    {
        $apiKey = env('TMDB_API_KEY');
        if (!$apiKey) {
            Log::warning('TMDB_API_KEY non configurata');
            return null;
        }

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
                $regista     = null;

                // ✅ Fetch regista/creator
                $tmdbId = $result['id'] ?? null;
                if ($tmdbId) {
                    if ($categoria === 'serie_tv') {
                        $creators = $result['created_by'] ?? [];
                        if (!empty($creators)) {
                            $regista = collect($creators)->pluck('name')->join(', ');
                        }

                        // Se non c'è created_by, prendi dal detail endpoint
                        if (empty($regista)) {
                            $detail = Http::timeout(5)->get("https://api.themoviedb.org/3/tv/{$tmdbId}", [
                                'api_key'  => $apiKey,
                                'language' => 'it-IT',
                            ]);
                            if ($detail->successful()) {
                                $creators = $detail->json()['created_by'] ?? [];
                                if (!empty($creators)) {
                                    $regista = collect($creators)->pluck('name')->join(', ');
                                }
                            }
                        }
                    } else {
                        $credits = Http::timeout(5)->get("https://api.themoviedb.org/3/movie/{$tmdbId}/credits", [
                            'api_key'  => $apiKey,
                        ]);
                        if ($credits->successful()) {
                            $crew     = $credits->json()['crew'] ?? [];
                            $director = collect($crew)->firstWhere('job', 'Director');
                            if ($director) {
                                $regista = $director['name'];
                            }
                        }
                    }
                }

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
                    'regista'     => $regista,
                ];
            }
        }

        return null;
    }

    // ============ WIKIPEDIA ============
    private function fetchWikipediaPlot($content)
    {
        $titleVariants = [
            "{$content->titolo} ({$content->anno} film)",
            "{$content->titolo} (film)",
            "{$content->titolo} ({$content->anno})",
            $content->titolo,
        ];

        foreach ($titleVariants as $title) {
            $summary = $this->getWikipediaSummary($title, 'it');
            if ($summary) return $summary;
        }

        $searchTitle = $this->searchWikipediaTitle($content->titolo, 'it');
        if ($searchTitle) {
            return $this->getWikipediaSummary($searchTitle, 'it');
        }

        foreach ($titleVariants as $title) {
            $summary = $this->getWikipediaSummary($title, 'en');
            if ($summary) return $summary;
        }

        $searchTitleEn = $this->searchWikipediaTitle($content->titolo, 'en');
        if ($searchTitleEn) {
            return $this->getWikipediaSummary($searchTitleEn, 'en');
        }

        return null;
    }

    private function getWikipediaSummary($title, $lang = 'it')
    {
        $response = Http::timeout(5)->get("https://{$lang}.wikipedia.org/w/api.php", [
            'action'      => 'query',
            'prop'        => 'extracts',
            'format'      => 'json',
            'exintro'     => true,
            'explaintext' => true,
            'redirects'   => true,
            'titles'      => $title,
        ]);

        if ($response->successful()) {
            $pages = $response->json()['query']['pages'] ?? [];
            foreach ($pages as $page) {
                if (!empty($page['extract'])) return $page['extract'];
            }
        }

        return null;
    }

    private function searchWikipediaTitle($query, $lang = 'it')
    {
        $response = Http::timeout(5)->get("https://{$lang}.wikipedia.org/w/api.php", [
            'action'   => 'query',
            'list'     => 'search',
            'srsearch' => $query,
            'format'   => 'json',
        ]);

        if ($response->successful()) {
            return $response->json()['query']['search'][0]['title'] ?? null;
        }

        return null;
    }

    // ============ YOUTUBE ============
    private function findYouTubeTrailerLink($titolo)
    {
        $apiKey = env('YOUTUBE_API_KEY');
        if (!$apiKey) {
            Log::warning('YOUTUBE_API_KEY non configurata');
            return null;
        }

        $query    = urlencode("{$titolo} trailer italiano");
        $url      = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=1&q={$query}&key={$apiKey}";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $videoId = $response->json()['items'][0]['id']['videoId'] ?? null;
            return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
        }

        return null;
    }
}