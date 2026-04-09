<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée les tables nécessaires aux files d'attente (queues) Laravel.
 * Non utilisé activement dans ce projet, mais requis par le framework.
 */
return new class extends Migration
{
    public function up(): void
    {
        // File d'attente principale des jobs asynchrones
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();        // Nom de la file
            $table->longText('payload');             // Job sérialisé
            $table->unsignedTinyInteger('attempts'); // Nombre de tentatives
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Permet de regrouper des jobs en lots (batch processing)
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Archive les jobs qui ont échoué (pour analyse et relance manuelle)
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
