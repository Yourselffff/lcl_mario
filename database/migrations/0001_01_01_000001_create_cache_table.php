<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée les tables nécessaires au driver de cache 'database'.
 * Utilisé si CACHE_STORE=database dans le .env.
 * Dans ce projet, le cache est utilisé pour stocker temporairement
 * la liste des films et inventaires (évite des appels API répétés).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Table principale du cache (clé → valeur sérialisée + expiration)
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Table des verrous de cache (évite les race conditions sur le cache)
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
