<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Content;
use App\Models\Tag;

class ImportMovies extends Command
{
    protected $signature = 'import:movies';
    protected $description = 'Import di film da csv';

    public function handle()
    {
        $path = storage_path('movies.csv');

        if (!file_exists($path)) {
            $this->error("File not found at $path");
            return 1;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); // lettura headers

        while ($row = fgetcsv($file)) {
            $data = array_combine($header, $row);

            $title = $data['Title'] ?? null;
            $year = $data['Year'] ?? null;
            $director = $data['Directors'] ?? null;
            $genresString = $data['Genres'] ?? null;

            if (!$title || !$year || !$director || !$genresString) {
                continue;
            }

            // Crea l'entry del contenuto 
            $content = Content::create([
                'titolo' => $title,
                'anno' => $year,
                'regista' => $director,
            ]);

            // Processa e inserisce i generi 
            
            $genres = explode(',', $genresString);
            $genreIds = [];

            foreach ($genres as $genreName) {
                $genreName = trim($genreName);
                if (empty($genreName)) continue;

                // Cerca il tag o lo crea nel DB se non esiste
                $tag = Tag::firstOrCreate(
                    ['name' => $genreName],
                    ['description' => "Genere $genreName"]
                );

                $genreIds[] = $tag->id;
            }

            if (!empty($genreIds)) {
                $content->tags()->attach($genreIds);
            }
        }
        fclose($file);
        $this->info("Movies imported successfully.");
        return 0;
    }
}

