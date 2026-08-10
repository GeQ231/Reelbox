<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';

    // ✅ FIX - nomi colonne corretti dal DB
    protected $fillable = [
        'titolo',
        'categoria',
        'descrizione', // ✅ era 'trama'
        'poster',      // ✅ era 'image'
        'trailer_url', // ✅ aggiunto
        'regista',
        'anno',
        'created_at',
        'updated_at'
    ];

    public function scopeFilms($query)
    {
        return $query->where('categoria', 'film');
    }

    public function scopeTvShows($query)
    {
        return $query->where('categoria', 'tv');
    }

    // ✅ FIX - usa Preference con liked=true
    public function likes()
    {
        return $this->hasMany(\App\Models\Preference::class)->where('liked', true);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class);
    }
}