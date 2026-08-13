<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikePost extends Model
{
    protected $table = 'like_post';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'post_id',
    ];
}