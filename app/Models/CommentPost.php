<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentPost extends Model
{
    use HasFactory;

    protected $table = 'comment_posts';

    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
    ];

    // Relazione con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relazione con Post
    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }
}
