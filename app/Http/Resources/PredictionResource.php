<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'variete' => $this->variete,
        'date' => $this->created_at->format('d/m/Y H:i'),
        'input_summary' => [
            'semaine_actuelle' => $this->input_data['current_week_data']['Semaine'],
            'rendement_actuel' => $this->input_data['current_week_data']['Rendement_t_ha'],
        ],
        'previsions' => $this->forecast_result, // Ton tableau de 4 semaines
    ];
}
}
