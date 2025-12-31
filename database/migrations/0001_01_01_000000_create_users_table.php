<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (Création de la table)
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password');

            // On ajoute tes champs de profil
            $table->string('firstname');
            $table->string('lastname');
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();

            // Rôles et statut
            $table->enum('role', ['user', 'admin'])->default('user'); // user ou admin
            $table->boolean('verified')->default(true);

            // Gère automatiquement created_at et updated_at
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations (Suppression si erreur)
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
