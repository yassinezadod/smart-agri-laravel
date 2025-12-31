<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('yield_comparisons', function (Blueprint $table) {
        // Renomme la colonne sans supprimer les données
        $table->renameColumn('r2_score', 'rmse');
    });
}

public function down(): void
{
    Schema::table('yield_comparisons', function (Blueprint $table) {
        // Permet de revenir en arrière si besoin
        $table->renameColumn('rmse', 'r2_score');
    });
}
};
