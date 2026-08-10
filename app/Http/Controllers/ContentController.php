<?php

namespace App\Http\Controllers;

use App\Models\Content;
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
    $content = Content::with('tags')->findOrFail($content->id);

    // ✅ FIX - usa 'descrizione' invece di 'trama'
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

    // ✅ FIX - usa 'trailer_url' (già corretto)
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

    return view('films.show', compact('content'));
}

    private function fetchWikipediaPlot($content)
    {
        $titleVariants = [
            "{$content->titolo} ({$content->anno} film)",
            "{$content->titolo} (film)",
            "{$content->titolo} ({$content->anno})",
            $content->titolo,
        ];

        // 1. Prova prima in ITALIANO
        foreach ($titleVariants as $title) {
            // ✅ FIX - passa $lang correttamente
            $summary = $this->getWikipediaSummary($title, 'it');
            if ($summary) return $summary;
        }

        $searchTitle = $this->searchWikipediaTitle($content->titolo, 'it');
        if ($searchTitle) {
            return $this->getWikipediaSummary($searchTitle, 'it');
        }

        // 2. FALLBACK in INGLESE
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

    // ✅ FIX - aggiunto parametro $lang con default 'it'
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

    // ✅ FIX - aggiunto parametro $lang con default 'it'
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

    private function findYouTubeTrailerLink($titolo)
    {
        $apiKey = env('YOUTUBE_API_KEY');
        if (!$apiKey) return null;

        $query    = urlencode("{$titolo} official trailer");
        $url      = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=1&q={$query}&key={$apiKey}";

        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $videoId = $response->json()['items'][0]['id']['videoId'] ?? null;
            return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
        }

        return null;
    }
}