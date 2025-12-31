<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Prediction;
use App\Models\YieldComparison;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 * name="Analyse de Rendement",
 * description="Gestion des comparaisons entre les prévisions IA et les rendements réels"
 * )
 */

class YieldComparisonController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/yield/compare",
     * summary="Comparer prévisions IA et données réelles",
     * description="Envoie les données réelles au service Python, calcule les métriques (MAE, RMSE) et enregistre le résultat.",
     * tags={"Analyse de Rendement"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"prediction_id", "valeurs_reelles"},
     * @OA\Property(property="prediction_id", type="string", format="uuid", example="019b6fe7-f207-73c5-b5bc-8fc776729eb5"),
     * @OA\Property(
     * property="valeurs_reelles",
     * type="array",
     * @OA\Items(
     * @OA\Property(property="semaine", type="integer", example=49),
     * @OA\Property(property="valeur_reelle", type="number", format="float", example=7.9)
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Analyse enregistrée ou mise à jour avec succès",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Analyse enregistrée avec succès"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(response=400, description="Données invalides ou aucune correspondance trouvée"),
     * @OA\Response(response=500, description="Erreur interne ou service IA indisponible")
     * )
     */

    public function compare(Request $request)
{
    // 1. Validation
    $request->validate([
        'prediction_id'   => 'required|exists:predictions,id',
        'valeurs_reelles' => 'required|array|min:1',
        'valeurs_reelles.*.semaine'       => 'required|integer',
        'valeurs_reelles.*.valeur_reelle' => 'required|numeric',
    ]);

    // 2. Récupérer la prédiction
    $prediction = Prediction::find($request->prediction_id);

    // 3. Fusionner (Correction : On utilise 'previsions' car c'est le nom dans votre historique)
    $mises_a_jour = [];
    $previsionsIA = $prediction->previsions ?? $prediction->forecast_result;

    foreach ($request->valeurs_reelles as $reel) {
        $match = collect($previsionsIA)->firstWhere('semaine', (int)$reel['semaine']);

        if ($match) {
            $mises_a_jour[] = [
                'semaine'          => (int)$reel['semaine'],
                'rendement_predit' => (float)$match['rendement_predit'],
                'valeur_reelle'    => (float)$reel['valeur_reelle']
            ];
        }
    }

    if (empty($mises_a_jour)) {
        return response()->json(['error' => 'Aucune correspondance de semaine trouvée dans la base'], 400);
    }

    // 4. Appel au Microservice Python
    $urlPython = config('services.ai_service.url') . '/api/compare';

    try {
        $response = Http::timeout(5)->post($urlPython, [
            'variete'      => $prediction->variete,
            'mises_a_jour' => $mises_a_jour
        ]);

        if ($response->successful()) {
            $resultIA = $response->json();


            $comparison = YieldComparison::updateOrCreate(
    ['predict_id' => $prediction->id], // 1. On cherche par cet ID
    [                                  // 2. On met à jour ces valeurs
        'variete'             => $prediction->variete,
        'mae'                 => $resultIA['performance_globale']['mae'],
        'rmse'            => $resultIA['performance_globale']['rmse'], // Stocke le RMSE
        'fiabilite'           => (float) str_replace('%', '', $resultIA['performance_globale']['fiabilite']),
        'comparaison_details' => $resultIA['comparaison_hebdomadaire']
    ]
);

                 // ============================================================
                //  ÉTAPE AUTO-SYNC : On prévient Python de mettre à jour MongoDB
                // ============================================================
                try {
                    Http::withHeaders([
                        'X-Internal-Sync-Key' => env('INTERNAL_SYNC_KEY')
                    ])->post(config('services.ai_service.url') . '/api/sync-history');

                    Log::info("Signal de synchronisation envoyé au service IA.");
                } catch (\Exception $eSync) {
                    Log::error("Échec du signal auto-sync : " . $eSync->getMessage());
                }
                // ============================================================

            return response()->json([
                'success' => true,
                'message' => 'Analyse enregistrée avec succès',
                'data'    => $comparison
            ]);
        } else {
            return response()->json([
                'error' => 'Erreur de réponse du serveur Python',
                'details' => $response->json()
            ], $response->status());
        }

    } catch (\Exception $e) {
        // ICI ON VOIT LA VRAIE ERREUR DANS POSTMAN
        Log::error("Erreur de connexion IA: " . $e->getMessage());
        return response()->json([
            'error' => 'Le service Python a un problème ou l\'URL est incorrecte',
            'message_technique' => $e->getMessage(),
            'url_appelee' => $urlPython
        ], 500);
    }
}

/**
     * @OA\Get(
     * path="/api/yield/comparisons",
     * summary="Lister les analyses de l'utilisateur",
     * description="Récupère toutes les comparaisons de rendement liées aux prédictions de l'utilisateur connecté.",
     * tags={"Analyse de Rendement"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Liste des analyses récupérée avec succès",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", type="array", @OA\Items(type="object"))
     * )
     * )
     * )
     */

    public function index()
{
    // Récupérer toutes les comparaisons de l'utilisateur connecté
    // On charge aussi la relation 'prediction' pour avoir tous les détails
    $comparisons = YieldComparison::with('prediction')
        ->whereHas('prediction', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $comparisons
    ]);
}


public function getAllDataForSync(Request $request)
{
    // 1. Vérifier si la clé est présente et correcte
    $providedKey = $request->header('X-Internal-Sync-Key');
    $secretKey = env('INTERNAL_SYNC_KEY');

    if (!$providedKey || $providedKey !== $secretKey) {
        return response()->json(['error' => 'Unauthorized Access'], 403);
    }

    // 2. Récupérer les données avec les relations nécessaires
    $data = YieldComparison::with('prediction')->get();

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}



}
