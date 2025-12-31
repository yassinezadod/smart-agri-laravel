<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YieldComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'predict_id',
        'variete',
        'mae',
        'rmse',
        'fiabilite',
        'comparaison_details'
    ];

    protected $casts = [
        'comparaison_details' => 'array',
        'mae' => 'float',
        'rmse' => 'float',
        'fiabilite' => 'float',
    ];

    /**
     * Relation avec le modèle Prediction (table predictions)
     */
    public function prediction(): BelongsTo
    {
        // Correction ici : On utilise Prediction::class et non Predict::class
        return $this->belongsTo(Prediction::class, 'predict_id');
    }
}
