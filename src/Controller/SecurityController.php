<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): Response
    {
        // Cette méthode ne sera jamais exécutée car le firewall interceptera la requête
        // Le success_handler et failure_handler configurés dans security.yaml s'en occupent
        
        // En cas d'erreur dans la configuration, on retourne une erreur explicite
        return $this->json([
            'message' => 'Cette méthode ne devrait jamais être appelée directement.'
        ], Response::HTTP_UNAUTHORIZED); //code 401
    }

// Route pour la déconnexion côté frontend (par exemple suppression du token JWT côté client)
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Le frontend supprimera le token JWT à la réception de cette réponse
        return new JsonResponse([
            'message' => 'Déconnexion réussie'
        ], JsonResponse::HTTP_OK); // Code 200 OK
    }
}