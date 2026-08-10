<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikePost extends Model
{
    protected $table = 'like_post'; // o 'like_posts' se così si chiama la tabella

    protected $fillable = ['user_id', 'post_id'];

}
