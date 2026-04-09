<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modèle Eloquent représentant un membre du personnel (table 'staff').
 * Utilisé uniquement si une BDD locale est configurée.
 * Dans ce projet, l'authentification passe par l'API Toad (ToadUser / ToadUserProvider).
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Table et clé primaire correspondant à la structure Staff de Sakila. */
    protected $table      = 'staff';
    protected $primaryKey = 'staff_id';

    /** Champs autorisés en assignation de masse. */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

    /** Champs exclus de la sérialisation JSON (ne jamais exposer le mot de passe). */
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
