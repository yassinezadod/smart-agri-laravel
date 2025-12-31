<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/admin/users",
     * summary="Liste de tous les utilisateurs (Admin uniquement)",
     * tags={"Administration"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Liste récupérée avec succès"
     * ),
     * @OA\Response(response=403, description="Accès refusé")
     * )
     */
    public function index()
    {
        // On récupère tous les utilisateurs, classés par date de création
        // On utilise la pagination pour la performance
        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'count' => $users->count(),
            'data' => $users
        ]);
    }
}
