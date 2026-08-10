<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Like;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class FeedController extends Controller
{
    //serve per gesire il feed dell utente (determina quali contenuti mostrare)


    public function search(Request $request)
{

    Log::info("Search method called.");

    // Leggi i parametri dalla query string
    $query = $request->query('query');
    $tipologia = $request->query('tipologia');
    $anno = $request->query('anno');
    $genere = $request->query('genere');
    $userId = $request->query('user_id');

    Log::info("Search parameters: ", $request->all());

    // Costruisco la query di base
    $contentsQuery = Content::withCount('likes');

    // Se almeno un filtro è presente, applico filtri
    if ($query || $tipologia || $anno || $genere) {
        Log::info("Applying filters to Content query...");
        
        //la barra di ricerca effettua la ricerca su titolo e trama
        if ($query) {
            $contentsQuery->where('titolo', 'like', "%{$query}%")
                          ->orWhere('trama', 'like', "%{$query}%");
        }

        if ($tipologia) {
            // film o serie tv
            $contentsQuery->where('categoria', $tipologia);
        }

        if ($anno) {
            // anno
            $contentsQuery->where('anno', $anno);
        }

        if ($genere) {
            $contentsQuery->whereHas('tags', function ($query) use ($genere) {
                $query->where('tags.id', $genere);
            });
        }
        

        // Prendi un contenuto random fra quelli filtrati
        $count = $contentsQuery->count();
        Log::info("Filtered content count: $count");
        $offset = rand(0, max(0, $count - 1));
        Log::info("Random offset: $offset");
        $content = $contentsQuery->skip($offset)->first();
        Log::info("Random content selected: " . ($content->titolo ?? 'none'));



        if (!$content) {
            return response()->json(['error' => 'Nessun contenuto corrisponde ai criteri di ricerca'], 404);
        }

    } else {
        // Nessun filtro: prendi un contenuto casuale
        $content = $contentsQuery->inRandomOrder()->first();

        if (!$content) {
            return response()->json(['error' => 'Nessun contenuto disponibile'], 404);
        }
    }

    // Se manca, allora faccio fetch dell'immagine
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


    $userHasLiked = false;

    if ($userId) {
        $userHasLiked = $content->favoritedBy()->where('user_id', $userId)->exists();
    }
    //Ricerca su wikipedia, diversa dall'altra perchè qua si cerca un summary, non la trama intera
    $WikipediaPlot = $this->fetchWikipediaPlot($content->titolo);
    

    return response()->json([
        'id' => $content->id,
        'titolo' => $content->titolo,
        'trama' => $WikipediaPlot,
        'image' => $content->image,
        'likes_count' => $content->likes_count,
        'user_has_liked' => $userHasLiked,
    ]);
}



    //funzione che mi ritorna i tags per la select della home

    public function getTags()
    {
        $tags = Tag::select('id', 'name')->get();
        return response()->json($tags);
    }



    private function fetchWikipediaImage($title)
    {
        //Possibili varianti del film da cercare
        $variants = [
            "$title (film)",
            "$title (movie)",
            $title
        ];

        // Provo con ogni variante
        foreach ($variants as $variant) {
            $image = $this->getImageFromWikipediaSummary($variant);
            if ($image) {
                Log::info("Fetched image for '$title' from variant '$variant': $image");
                return $image;
            }
        }

        // Fallback: cferco su wikipedia per il miglior match
        $searchedTitle = $this->searchWikipediaTitle($title);
        if ($searchedTitle) {
            $image = $this->getImageFromWikipediaSummary($searchedTitle);
            if ($image) {
                Log::info("Fetched image for '$title' via search match '$searchedTitle': $image");
                return $image;
            }
        }

        Log::warning("No image found for: $title");
        return null;
    }

    private function getImageFromWikipediaSummary($title)
    {
        $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($title);
        $response = Http::timeout(3)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['thumbnail']['source'] ?? null;
        }

        return null;
    }

    private function searchWikipediaTitle($query)
    {
        //Identica a quella della pagina del singolo film
        $url = "https://en.wikipedia.org/w/api.php";
        $params = [
            'action' => 'query',
            'list' => 'search',
            'srsearch' => $query,
            'format' => 'json'
        ];

        $response = Http::timeout(3)->get($url, $params);

        if ($response->successful()) {
            $results = $response->json();
            return $results['query']['search'][0]['title'] ?? null;
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
            //Identica a quella della pagina del singolo film
            $response = Http::get('https://en.wikipedia.org/w/api.php', [
                'action' => 'query',
                'prop' => 'extracts',
                'format' => 'json',
                'exintro' => true,
                'explaintext' => true,
                'redirects' => true,
                'titles' => $variant,
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