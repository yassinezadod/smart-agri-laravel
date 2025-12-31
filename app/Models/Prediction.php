<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Prediction extends Model
{
    use HasUuids;

    protected $keyType = 'string'; // Précise que la clé n'est plus un entier
    public $incrementing = false;  // Désactive l'auto-incrémentation
    protected $fillable = [
        'user_id',
        'variete',
        'input_data',
        'forecast_result'
    ];

    // Important : On demande à Laravel de convertir le JSON en Array automatiquement
    protected $casts = [
        'input_data' => 'array',
        'forecast_result' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
