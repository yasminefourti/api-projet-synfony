<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        // Vous pourriez vouloir restreindre cette route aux administrateurs
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $users = $userRepository->findAll();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'role' => $user->getRoles(),
            ];
        }
        return $this->json($data);
    }
    
    /**
     * Affiche les informations de l'utilisateur actuellement connecté
     */
    #[Route('/api/user/profile', name: 'api_user_profile', methods: ['GET'])]
    public function getCurrentUserProfile(): JsonResponse
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        
        // Vérifie si un utilisateur est connecté
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        // Prépare les données à retourner
        $userData = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
            // Vous pouvez ajouter d'autres champs selon votre entité User
        ];
        
        // Retourne les informations de l'utilisateur
        return $this->json([
            'user' => $userData
        ]);
    }
}