<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';

    protected $fillable = ['titolo', 'categoria', 'trama', 'image', 'regista', 'anno', 'created_at', 'updated_at'];

    

    public function scopeFilms($query)
    {
        return $query->where('categoria', 'film');
    }

    /**
     * Scope per filtrare solo serie TV
     */
    public function scopeTvShows($query)
    {
        return $query->where('categoria', 'tv');
    }

    // Funzione per gestire i favoriti 
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // Gestione dei tag
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}