<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modèle Eloquent utilisateur par défaut généré par Laravel.
 * Conservé comme référence ; le projet utilise User.php (table 'staff') à la place.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Champs autorisés en assignation de masse. */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** Champs exclus de la sérialisation JSON. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
