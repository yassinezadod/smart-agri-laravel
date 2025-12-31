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
        Schema::create('yield_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('predict_id')->constrained('predictions')->onDelete('cascade');
            $table->string('variete');
            $table->float('mae');
            $table->float('r2_score');
            $table->float('fiabilite');
            $table->json('comparaison_details'); // Stockage du mix Predit vs Réel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yield_comparisons');
    }
};
