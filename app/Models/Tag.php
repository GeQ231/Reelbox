<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];

    
    //questa 
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tag_preferences', 'tag_id', 'user_id');
    }

    //Relazione per inserire tag_id nella tabella dei contenuti 
    public function contents()
    {
        return $this->belongsToMany(Content::class);
    }

}
