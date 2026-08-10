<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateContents extends Command
{
    protected $signature = 'clean:duplicates';
    protected $description = 'Remove duplicate contents by titolo and anno';

    public function handle()
    {
        $this->info("Starting cleanup...");

        $duplicates = DB::table('contents')
            ->select('titolo', 'anno', DB::raw('MIN(id) as keep_id'))
            ->groupBy('titolo', 'anno')
            ->havingRaw('COUNT(*) > 1')
            ->limit(500) // only process 500 duplicates at a time
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('contents')
                ->where('titolo', $dup->titolo)
                ->where('anno', $dup->anno)
                ->where('id', '!=', $dup->keep_id)
                ->limit(100) 
                ->delete();
        }

        $this->info("Duplicates cleaned.");
    }
}

