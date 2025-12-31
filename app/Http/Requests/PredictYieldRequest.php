<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PredictYieldRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        // On laisse true car la route est déjà protégée par le middleware 'auth:sanctum'
        return true;
    }

    /**
     * Définit les règles de validation.
     */
    public function rules(): array
    {
        // 1. Liste exhaustive des champs climatiques attendus par ton modèle IA
        $climateFields = [
            'ETo_mm', 'Temp_Min_C', 'Temp_Moy_C', 'Temp_Max_C',
            'Hum_Min_pct', 'Hum_Moy_pct', 'Hum_Max_pct', 'Rayonnement_global',
            'VPD_Min', 'VPD_Kpa', 'VPD_Max', 'Degre_jour',
            'Cumul_degres_jour', 'Amplitude_thermique', 'Indice_chaleur', 'Point_de_rosee'
        ];

        // 2. Règles de base pour la structure globale
        $rules = [
            'current_week_data' => 'required|array',
            'current_week_data.Semaine' => 'required|integer|between:1,52',
            'current_week_data.Jour_apres_plantation' => 'required|integer',
            'current_week_data.Vitesse_de_maturation' => 'required|integer',
            'current_week_data.variete' => 'required|string',
            'current_week_data.Rendement_t_ha' => 'required|numeric',

            'predictions_input' => 'required|array',
            'predictions_input.S1' => 'required|array',
            'predictions_input.S2' => 'required|array',
            'predictions_input.S3' => 'required|array',
            'predictions_input.S4' => 'required|array',
        ];

        // 3. Application automatique de la validation numérique pour chaque champ climatique
        // Cela s'applique à la semaine actuelle ET aux 4 semaines futures (S1 à S4)
        foreach ($climateFields as $field) {
            $rules["current_week_data.$field"] = 'required|numeric';
            $rules["predictions_input.S1.$field"] = 'required|numeric';
            $rules["predictions_input.S2.$field"] = 'required|numeric';
            $rules["predictions_input.S3.$field"] = 'required|numeric';
            $rules["predictions_input.S4.$field"] = 'required|numeric';
        }

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés (Optionnel)
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire pour la prédiction.',
            'numeric'  => 'Le champ :attribute doit être un nombre décimal.',
            'integer'  => 'Le champ :attribute doit être un nombre entier.',
        ];
    }
}
