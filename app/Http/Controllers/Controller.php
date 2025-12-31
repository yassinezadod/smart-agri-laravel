<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Yield AI API Documentation",
 * description="Documentation de l'API de prédiction de rendement agricole",
 * @OA\Contact(email="admin@yield.ai")
 * )
 *
 * @OA\Server(
 * url="http://127.0.0.1:8001",
 * description="Serveur de développement local"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Entrez votre token d'accès (access_token) reçu lors de la connexion pour accéder aux routes protégées."
 * )
 */
abstract class Controller
{
    // ...
}
