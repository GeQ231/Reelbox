<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'content_id'];

    // Relazione per accedere al contenuto associato al like
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
