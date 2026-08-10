<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Content; 


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    //questa funzione mi permette di ottenere le preferenze dell utente (oggetti id->tag)
    //ritorna oggetto tag che ha gli attributi name e description
    public function favorites()
    {
        return $this->belongsToMany(\App\Models\Tag::class, 'users_tags_preferences', 'user_id', 'tag_id');
    }   

    
    public function preferences()
    {
        return $this->belongsToMany(Tag::class, 'users_tags_preferences', 'user_id', 'tag_id');
    }

   public function isAdmin(): bool
    {
    return (bool) $this->is_admin; // ✅ null diventa false
    }


}


