<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // Campi che si possono assegnare in massa (mass assignment)
    protected $fillable = [
        'titolo',
        'contenuto',
        'user_id',
        'tag_id', // se usi le categorie
    ];
     // Cast del campo 'tags' a array
    protected $casts = [
        'tags' => 'array',
    ];
    // Relazione con l'utente (autore del post)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relazione con la categoria (se usi le categorie)
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
    // Like fatti dall'utente
public function likes()
{
    return $this->hasMany(LikePost::class);
}

// Commenti fatti dall'utente
public function comments()
{
    return $this->hasMany(CommentPost::class, 'post_id');
}

}
