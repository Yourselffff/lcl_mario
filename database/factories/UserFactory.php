<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory pour générer des utilisateurs fictifs en base de données.
 * Utilisé uniquement dans les tests et le seeder de développement.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /** Mot de passe partagé entre toutes les instances de la factory (évite le rehachage). */
    protected static ?string $password;

    /**
     * Définit les valeurs par défaut d'un utilisateur généré.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * État "email non vérifié" : l'utilisateur n'a pas encore cliqué sur le lien de confirmation.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
