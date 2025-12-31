<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmailNotification; // Ne pas oublier cet import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResetPasswordCodeNotification;
use Carbon\Carbon;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Inscription d'un nouvel utilisateur",
     * tags={"Authentification"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password","firstname","lastname"},
     * @OA\Property(property="email", type="string", format="email", example="agriculteur1442@yield.ai"),
     * @OA\Property(property="password", type="string", format="password", example="password64123"),
     * @OA\Property(property="firstname", type="string", example="Karima"),
     * @OA\Property(property="lastname", type="string", example="Faridi"),
     * @OA\Property(property="ville", type="string", example="Taroudant"),
     * @OA\Property(property="phone", type="string", example="06123454678"),
     * @OA\Property(property="adresse", type="string", example="Quartier Industriel"),
     * @OA\Property(property="bio", type="string", example="Producteur d'agrumes.")
     * )
     * ),
     * @OA\Response(response=201, description="Utilisateur enregistré, email envoyé"),
     * @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
            'firstname' => 'required|string|max:50',
            'lastname'  => 'required|string|max:50',
            'ville'     => 'nullable|string',
            'phone'     => 'nullable|string',
            'adresse'   => 'nullable|string',
            'bio'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'ville'     => $request->ville,
            'phone'     => $request->phone,
            'adresse'   => $request->adresse,
            'bio'       => $request->bio,
            'role'      => 'user',
            'verified'  => false, // Important : false par défaut
        ]);

        // --- AJOUT ICI : Envoi de l'email ---
        $user->notify(new VerifyEmailNotification());

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur enregistré. Veuillez vérifier votre email pour activer votre compte.',
            'user' => $user
        ], 201);
    }

    /**
     * @OA\Get(
     * path="/api/verify-email/{id}",
     * summary="Activer le compte utilisateur",
     * description="Active le compte de l'utilisateur en passant son UUID",
     * tags={"Authentification"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="UUID de l'utilisateur à vérifier",
     * required=true,
     * @OA\Schema(type="string", format="uuid", example="9ade3652-f99a-4c28-9f66-1c290130965c")
     * ),
     * @OA\Response(response=200, description="Compte activé"),
     * @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function verify($id)
    {
        // findOrFail fonctionne parfaitement avec les UUID car le modèle User utilise HasUuids
        $user = User::findOrFail($id);

        if (!$user->verified) {
            $user->verified = true;
            // On peut aussi ajouter une date de vérification si vous ajoutez la colonne 'email_verified_at'
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Votre compte est activé ! Vous pouvez maintenant vous connecter.'
        ]);
    }


    /**
 * @OA\Post(
 * path="/api/login",
 * summary="Authentification de l'utilisateur",
 * description="Permet à un agriculteur de se connecter pour obtenir un token",
 * tags={"Authentification"},
 * @OA\RequestBody(
 * required=true,
 * @OA\JsonContent(
 * required={"email","password"},
 * @OA\Property(property="email", type="string", format="email", example="agriculteur1442@yield.ai"),
 * @OA\Property(property="password", type="string", format="password", example="password64123")
 * ),
 * ),
 * @OA\Response(
 * response=200,
 * description="Succès",
 * @OA\JsonContent(
 * @OA\Property(property="access_token", type="string"),
 * @OA\Property(property="token_type", type="string", example="Bearer")
 * )
 * ),
 * @OA\Response(response=401, description="Identifiants invalides")
 * )
 */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        if (!$user->verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Votre compte n\'est pas encore activé. Veuillez vérifier vos emails.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Déconnexion de l'utilisateur",
     * description="Révoque le token d'accès actuel de l'utilisateur",
     * tags={"Authentification"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Déconnexion réussie",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Déconnecté avec succès")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Non authentifié (Token invalide ou manquant)"
     * )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnecté avec succès'
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/forgot-password",
     * summary="Demander un code de récupération de mot de passe",
     * tags={"Authentification"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(@OA\Property(property="email", type="string", format="email", example="agriculteur1442@yield.ai"))
     * ),
     * @OA\Response(response=200, description="Code envoyé"),
     * @OA\Response(response=404, description="Email non trouvé")
     * )
     */

    public function forgotPassword(Request $request) {
    $request->validate(['email' => 'required|email|exists:users']);

    // Générer un code à 6 chiffres
    $code = rand(100000, 999999);

    // Enregistrer ou mettre à jour le code dans la table
    DB::table('password_reset_codes')->updateOrInsert(
        ['email' => $request->email],
        ['code' => $code, 'created_at' => now()]
    );

    // Envoyer l'email (Assure-toi d'importer la Notification)
    $user = User::where('email', $request->email)->first();
    $user->notify(new ResetPasswordCodeNotification($code));

    return response()->json(['message' => 'Code envoyé à votre adresse email.']);
}

/**
     * @OA\Post(
     * path="/api/reset-password",
     * summary="Réinitialiser le mot de passe avec le code",
     * tags={"Authentification"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","code","password","password_confirmation"},
     * @OA\Property(property="email", type="string", format="email"),
     * @OA\Property(property="code", type="string", example="123456"),
     * @OA\Property(property="password", type="string", format="password"),
     * @OA\Property(property="password_confirmation", type="string", format="password")
     * )
     * ),
     * @OA\Response(response=200, description="Mot de passe réinitialisé"),
     * @OA\Response(response=422, description="Code invalide ou expiré")
     * )
     */

public function resetPassword(Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users',
        'code' => 'required|string',
        'password' => 'required|min:8|confirmed',
    ]);

    $record = DB::table('password_reset_codes')
                ->where('email', $request->email)
                ->where('code', $request->code)
                ->first();

    // Vérifier si le code existe et n'est pas expiré (30 min)
    if (!$record || now()->diffInMinutes($record->created_at) > 30) {
        return response()->json(['message' => 'Code invalide ou expiré.'], 422);
    }

    // Mettre à jour le mot de passe
    $user = User::where('email', $request->email)->first();
    $user->update(['password' => Hash::make($request->password)]);

    // Supprimer le code utilisé
    DB::table('password_reset_codes')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Mot de passe modifié avec succès.']);
}

/**
     * @OA\Get(
     * path="/api/users/me",
     * summary="Obtenir les informations du profil connecté",
     * tags={"Profil"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Détails du profil"),
     * @OA\Response(response=401, description="Non autorisé")
     * )
     */
public function me(Request $request)
{
    // Retourne les infos de l'utilisateur lié au Token JWT/Sanctum
    return response()->json([
        'status' => 'success',
        'user' => $request->user()
    ]);
}

/**
     * @OA\Patch(
     * path="/api/users/me",
     * summary="Mettre à jour mon profil",
     * tags={"Profil"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * @OA\JsonContent(
     * @OA\Property(property="firstname", type="string", example="Karima"),
     * @OA\Property(property="ville", type="string", example="Agadir"),
     * @OA\Property(property="bio", type="string", example="Bio mise à jour.")
     * )
     * ),
     * @OA\Response(response=200, description="Profil mis à jour"),
     * @OA\Response(response=422, description="Erreur de validation")
     * )
     */
public function updateProfile(Request $request)
{
    $user = $request->user();

    // 1. Validation (tous les champs sont optionnels car c'est un PATCH)
    $validator = Validator::make($request->all(), [
        'firstname' => 'sometimes|string|max:50',
        'lastname'  => 'sometimes|string|max:50',
        'ville'     => 'nullable|string',
        'phone'     => 'nullable|string',
        'adresse'   => 'nullable|string',
        'bio'       => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

    // 2. Mise à jour des données présentes dans la requête
    // On utilise fill() pour remplir les champs autorisés dans le modèle User
    $user->fill($request->only([
        'firstname', 'lastname', 'ville', 'phone', 'adresse', 'bio'
    ]));

    // Si l'utilisateur a modifié des données, on sauvegarde
    if ($user->isDirty()) {
        $user->save();
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Profil mis à jour avec succès',
        'user' => $user
    ]);
}

/**
     * @OA\Delete(
     * path="/api/users/me",
     * summary="Désactiver mon compte (Soft Delete)",
     * tags={"Profil"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Compte désactivé"),
     * @OA\Response(response=401, description="Non autorisé")
     * )
     */
public function deleteProfile(Request $request)
{
    $user = $request->user();

    // 1. Déconnexion de toutes les sessions (suppression des tokens)
    $user->tokens()->delete();

    // 2. Suppression logique (remplit deleted_at dans la base)
    $user->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Votre compte a été désactivé. Vos données historiques sont conservées.'
    ], 200);
}

}
