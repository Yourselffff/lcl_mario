<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder principal : peuple la base de données avec des données initiales.
 * Exécuté via la commande : php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Crée un utilisateur de test fixe pour le développement
        // User::factory(10)->create(); // Décommenter pour générer 10 utilisateurs aléatoires

        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
