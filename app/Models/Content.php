<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';

    // ✅ FIX - nomi colonne corretti dal DB
    protected $fillable = [
    'user_id',
    'content_id',
    'post_id',
    'body',
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

    // Deve avere questo:
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}