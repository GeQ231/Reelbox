<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Post.php
class Post extends Model
{
    protected $fillable = [
        'user_id',
        'titolo',
        'contenuto',
        'tag_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'like_post');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}


