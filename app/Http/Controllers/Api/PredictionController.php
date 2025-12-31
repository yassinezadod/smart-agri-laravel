<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\PredictYieldRequest;
use App\Models\Prediction;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PredictionResource;


/**
 * @OA\Tag(name="Predictions", description="Gestion des prédictions de rendement")
 */
class PredictionController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/predict-yield",
     * summary="Lancer une prédiction de rendement",
     * tags={"Predictions"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"variete", "current_week_data"},
     * @OA\Property(property="variete", type="string", example="Variété A"),
     * @OA\Property(property="current_week_data", type="object", description="Données climatiques de la semaine")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Prédiction réussie",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="result", type="object")
     * )
     * ),
     * @OA\Response(response=500, description="Erreur service IA")
     * )
     */
    public function predict(PredictYieldRequest $request)
    {
        // 1. URL du microservice Python
        $url = config('services.ai_service.url') . '/predict';

        try {
            // 2. Envoi de la requête à Python
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, $request->validated());

            if ($response->successful()) {
                $user = auth()->user();
                $aiData = $response->json();

                // Détection de la structure (que 'result' soit présent ou non)
                $actualData = isset($aiData['result']) ? $aiData['result'] : $aiData;

                // Sécurité : Vérifier si la clé forecast existe avant de sauvegarder
                if (!isset($actualData['forecast'])) {
                    Log::error("Structure JSON Python invalide", $aiData);
                    return response()->json([
                        'success' => false,
                        'message' => 'Format de réponse IA incorrect'
                    ], 500);
                }

                // 3. Sauvegarde dans MySQL pour l'historique d'Ali
                Prediction::create([
                    'user_id'         => $user->id,
                    'variete'         => $actualData['variete'] ?? $request->input('current_week_data.variete'),
                    'input_data'      => $request->validated(),
                    'forecast_result' => $actualData['forecast']
                ]);

                // 4. Réponse complète avec TOUTES les data de l'utilisateur
                return response()->json([
                    'success' => true,
                    'user' => $user->only(['id', 'firstname', 'lastname', 'email', 'ville', 'adresse', 'phone', 'bio']),
                    'result' => $aiData
                ]);
            }

            // Gestion de l'erreur du microservice
            return response()->json([
                'success' => false,
                'message' => 'Le microservice Python a renvoyé une erreur',
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            // Gestion de l'erreur de connexion (microservice éteint par exemple)
            Log::error("Erreur de connexion microservice : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Impossible de joindre le service IA'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/predictions",
     * summary="Récupérer l'historique des prédictions",
     * tags={"Predictions"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Liste des prédictions",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="history", type="array", @OA\Items(type="object"))
     * )
     * )
     * )
     */
    public function index()
{
    $user = auth()->user();

    // On récupère directement les prédictions paginées (10 par page)
    $predictions = $user->predictions()
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

    return response()->json([
        'success' => true,
        'user'    => $user->only(['id', 'firstname', 'lastname', 'email', 'ville', 'adresse', 'phone', 'bio']),
        'history' => PredictionResource::collection($predictions)->response()->getData(true)
    ]);
}
}
