<?php

namespace Database\Seeders;
use App\Models\Tag;


use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         Tag::insert([
        ['name' => 'Azione', 'description' => 'Film ricchi di azione e adrenalina'],
        ['name' => 'Commedia', 'description' => 'Film divertenti'],
        ['name' => 'Drammatico', 'description' => 'Film intensi e toccanti'],
        ['name' => 'Fantasy', 'description' => 'Mondi immaginari e magici'],
        ['name' => 'Horror', 'description' => 'Film spaventosi e inquietanti'],
    ]);
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
