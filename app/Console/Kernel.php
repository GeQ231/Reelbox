<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Import del comando 
use App\Console\Commands\ImportMovies;
use App\Console\Commands\CleanDuplicateContents; 

class Kernel extends ConsoleKernel
{
    /**
     * Registro dei comandi per l'applicazione
     */
    protected $commands = [
        ImportMovies::class, // registrazione del comando custom 
        CleanDuplicateContents::class,
    ];

    /**
     * Definizione della schedule dei comandi da eseguire
     */
    protected function schedule(Schedule $schedule): void
    {
        // Definisce la schedule se esistono(non ne abbiamo bisogno in questo caso) 
    }

    /**
     * Registro dei comandi nell'applicazione
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
