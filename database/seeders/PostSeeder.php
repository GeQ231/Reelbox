<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/PostSeeder.php
    public function run(): void
    {
    Post::create([
        'user_id' => 1,
        'title' => 'Post di esempio',
        'body' => 'Contenuto del post...',
        'tag' => 'Action',
    ]);
    }
}
