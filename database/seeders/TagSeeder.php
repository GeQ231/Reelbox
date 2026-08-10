<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run()
    {
        Tag::create(['name' => 'Azione', 'description' => 'Film ricchi di azione e adrenalina']);
        Tag::create(['name' => 'Commedia', 'description' => 'Film comici e divertenti']);
        Tag::create(['name' => 'Drammatico', 'description' => 'Film intensi ed emotivi']);
        Tag::create(['name' => 'Fantascienza', 'description' => 'Film futuristici o immaginari']);
        Tag::create(['name' => 'Horror', 'description' => 'Film spaventosi o inquietanti']);
    }
}
