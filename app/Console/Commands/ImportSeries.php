<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ImportSeries extends Command
{
    protected $signature = 'import:series';
    protected $description = 'Import serie TV da Netflix CSV';

    public function handle()
    {
        $path = storage_path('app/netflix_titles.csv');

        if (!file_exists($path)) {
            $this->error("File non trovato: $path");
            return 1;
        }

        $lines  = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = str_getcsv(array_shift($lines));
        $count  = 0;

        $this->info("Inizio importazione...");

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count($row) !== count($header)) continue;

            $data = array_combine($header, $row);

            $type = trim($data['type'] ?? '');
            if ($type !== 'TV Show') continue;

            $title = trim($data['title'] ?? '');
            if (empty($title)) continue;

            $year   = trim($data['release_year'] ?? '');
            $genres = trim($data['listed_in']    ?? '');
            $desc   = trim($data['description']  ?? '');

            try {
                // ✅ Insert diretto nel DB senza Eloquent
                $contentId = DB::table('contents')->insertGetId([
                    'titolo'     => $title,
                    'anno'       => $year ?: null,
                    'categoria'  => 'serie_tv',
                    'descrizione' => $desc ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($genres && $contentId) {
                    foreach (explode(',', $genres) as $genreName) {
                        $genreName = trim($genreName);
                        if (empty($genreName)) continue;

                        // ✅ Aggiungi questa riga!
                        $genreName = $this->normalizzaGenere($genreName);

                        $tag = Tag::firstOrCreate(
                            ['name' => $genreName],
                            ['description' => "Genere $genreName"]
                        );

                        // ✅ Insert pivot diretto
                        DB::table('content_tag')->insertOrIgnore([
                            'content_id' => $contentId,
                            'tag_id'     => $tag->id,
                        ]);
                    }
                }

                $count++;
                $this->info("✅ $count - $title");

            } catch (\Exception $e) {
                $this->error("Errore su '$title': " . $e->getMessage());
                continue;
            }
        }

        $this->info("✅ Completato! Importate $count serie TV.");
        return 0;
    }
    // Aggiungi questo metodo in entrambi i comandi
private function normalizzaGenere($genere): string
{
    $mappa = [
        'Action'            => 'Azione',
        'Adventure'         => 'Avventura',
        'Animation'         => 'Animazione',
        'Biography'         => 'Biografia',
        'Comedy'            => 'Commedia',
        'Crime'             => 'Crime',
        'Documentary'       => 'Documentario',
        'Drama'             => 'Drammatico',
        'Family'            => 'Famiglia',
        'Fantasy'           => 'Fantasy',
        'History'           => 'Storia',
        'Horror'            => 'Horror',
        'Music'             => 'Musica',
        'Musical'           => 'Musical',
        'Mystery'           => 'Mistero',
        'Romance'           => 'Romantico',
        'Sci-Fi'            => 'Fantascienza',
        'Sport'             => 'Sport',
        'Thriller'          => 'Thriller',
        'War'               => 'Guerra',
        'Western'           => 'Western',
        'Reality-TV'        => 'Reality',
        'Talk-Show'         => 'Talk Show',
        'Game-Show'         => 'Game Show',
        'News'              => 'Notizie',
        'Short'             => 'Cortometraggio',
        // Netflix genres
        'TV Dramas'                     => 'Drammatico',
        'TV Comedies'                   => 'Commedia',
        'TV Action & Adventure'         => 'Azione',
        'TV Horror'                     => 'Horror',
        'TV Mysteries'                  => 'Mistero',
        'TV Sci-Fi & Fantasy'           => 'Fantascienza',
        'TV Thrillers'                  => 'Thriller',
        'Romantic TV Shows'             => 'Romantico',
        'Crime TV Shows'                => 'Crime',
        'Docuseries'                    => 'Documentario',
        'Kids\' TV'                     => 'Famiglia',
        'Anime Series'                  => 'Anime',
        'International TV Shows'        => 'Internazionale',
        'British TV Shows'              => 'Britannico',
        'Korean TV Shows'               => 'Coreano',
        'Spanish-Language TV Shows'     => 'Spagnolo',
        'Stand-Up Comedy & Talk Shows'  => 'Commedia',
        'Teen TV Shows'                 => 'Teen',
        'Science & Nature TV'           => 'Natura',
        'Reality TV'                    => 'Reality',
        'Sports Movies'                 => 'Sport',
        'Documentaries'                 => 'Documentario',
        'Children & Family Movies'      => 'Famiglia',
        'Comedies'                      => 'Commedia',
        'Dramas'                        => 'Drammatico',
        'Thrillers'                     => 'Thriller',
        'Action & Adventure'            => 'Azione',
        'Sci-Fi & Fantasy'              => 'Fantascienza',
        'Anime Features'                => 'Anime',
        'Horror Movies'                 => 'Horror',
        'Romantic Movies'               => 'Romantico',
        'Music & Musicals'              => 'Musical',
        'Classic Movies'                => 'Classico',
        'Cult Movies'                   => 'Cult',
        'Faith & Spirituality'          => 'Spiritualità',
        'LGBTQ Movies'                  => 'LGBTQ',
        'Independent Movies'            => 'Indipendente',
        'International Movies'          => 'Internazionale',
    ];

    return $mappa[trim($genere)] ?? trim($genere);
}
}