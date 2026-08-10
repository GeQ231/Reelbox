<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];

    // ✅ FIX BUG 2 - nome tabella pivot corretto
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'users_tags_preferences', // ✅ era 'user_tag_preferences'
            'tag_id',
            'user_id'
        );
    }

    // Relazione con i contenuti
    public function contents()
    {
        return $this->belongsToMany(Content::class);
    }

    // Relazione con i post del forum
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}