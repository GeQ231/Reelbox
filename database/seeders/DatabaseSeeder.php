<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tags
        Tag::insert([
            ['name' => 'Azione',    'description' => 'Film ricchi di azione',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Commedia',  'description' => 'Film divertenti',           'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Drammatico','description' => 'Film intensi e toccanti',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fantasy',   'description' => 'Mondi immaginari e magici', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Horror',    'description' => 'Film spaventosi',           'created_at' => now(), 'updated_at' => now()],
        ]);

        // ✅ Admin
        $admin = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@reelbox.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Utente normale
        $user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Posts
        $azione   = Tag::where('name', 'Azione')->first();
        $horror   = Tag::where('name', 'Horror')->first();
        $commedia = Tag::where('name', 'Commedia')->first();

        Post::insert([
            [
                'user_id'    => $user->id,
                'titolo'     => 'Miglior film d\'azione del 2024?',
                'contenuto'  => 'Quali sono secondo voi i migliori film d\'azione?',
                'tag_id'     => $azione->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => $user->id,
                'titolo'     => 'Film horror consigliati',
                'contenuto'  => 'Cerco consigli su film horror!',
                'tag_id'     => $horror->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => $user->id,
                'titolo'     => 'Commedia preferita?',
                'contenuto'  => 'Qual è la vostra commedia preferita?',
                'tag_id'     => $commedia->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Importa film
        $this->command->call('import:movies');
        // ✅ Importa serie TV
        $this->command->call('import:series');
    }
}