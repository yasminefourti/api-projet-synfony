<?php
// src/Controller/UserController.php
namespace App\Controller;
use App\Entity\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    /**
     * Liste tous les utilisateurs (réservé aux administrateurs)
     */
    #[Route('/api/users', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): JsonResponse
    {

        
        $users = $userRepository->findAll();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles() ?? [],
            ];
        }
        return $this->json([
            'users' => $data,
            'total' => count($data)
        ]);
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
            'roles' => $user->getRoles(),
            // Vous pouvez ajouter d'autres champs selon votre entité User
        ];
        
        // Retourne les informations de l'utilisateur
        return $this->json([
            'user' => $userData
        ]);
    }

    /**
     * Met à jour les informations de l'utilisateur connecté
     */
    #[Route('/api/user/profile', name: 'api_user_update_profile', methods: ['PUT', 'PATCH'])]
    public function updateUserProfile(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher = null
    ): JsonResponse
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        
        // Vérifie si un utilisateur est connecté
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        // Récupère les données de la requête
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'message' => 'Données invalides ou manquantes'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Met à jour les champs si présents
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }
        
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        
        // Mise à jour du mot de passe si présent et si le service de hachage est disponible
        if (isset($data['password']) && !empty($data['password']) && $passwordHasher !== null) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        // Valide l'entité avant la sauvegarde
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Sauvegarde les modifications
        $entityManager->flush();
        
        // Retourne les informations mises à jour
        return $this->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }
     /**
     * Affiche les détails d'un utilisateur spécifique (réservé aux administrateurs)
     */
    #[Route('/api/users/{id}', name: 'user_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $userData = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            // Ajoutez d'autres champs selon besoin
        ];
        
        return $this->json([
            'user' => $userData
        ]);
    }

     /**
     * Active/désactive ou change le(s) rôle(s) d'un utilisateur (réservé aux administrateurs)
     */
    #[Route('/api/admin/users/{userId}', name: 'admin_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(
        int $userId,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $user = $userRepository->find($userId);
        
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Récupère les données de la requête
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'message' => 'Données invalides ou manquantes'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Mise à jour des rôles
        if (isset($data['roles']) && is_array($data['roles'])) {
            // Assurez-vous que les rôles sont valides
            $validRoles = ['ROLE_USER', 'ROLE_ADMIN']; // Ajoutez d'autres rôles si nécessaire
            $newRoles = array_filter($data['roles'], function($role) use ($validRoles) {
                return in_array($role, $validRoles);
            });
            
            // Toujours conserver ROLE_USER de base
            if (!in_array('ROLE_USER', $newRoles)) {
                $newRoles[] = 'ROLE_USER';
            }
            
            $user->setRoles($newRoles);
        }
        
        // Mise à jour du statut actif/inactif si présent
        if (isset($data['isActive'])) {
            // Supposons que vous ayez une méthode setActive dans votre entité User
            if (method_exists($user, 'setActive')) {
                $user->setActive((bool)$data['isActive']);
            }
            // Alternative: si vous utilisez un champ is_active
            // $user->setIsActive((bool)$data['isActive']);
        }
        
        // Valide l'entité avant la sauvegarde
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Sauvegarde les modifications
        $entityManager->flush();
        
        // Retourne les informations mises à jour
        return $this->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                // Ajoutez le statut actif si applicable
                // 'isActive' => $user->isActive(),
            ]
        ]);
    }

    /**
     * Suppression logique ou désactivation définitive d'un utilisateur (réservé aux administrateurs)
     */
    #[Route('/api/admin/users/{userId}', name: 'admin_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(
        int $userId,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse
    {
        $user = $userRepository->find($userId);
        
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Empêcher la suppression de son propre compte
        $currentUser = $this->getUser();
        if ($currentUser && $currentUser->getId() === $user->getId()) {
            return $this->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Récupérer le type de suppression depuis les paramètres (query parameters)
        $type = $request->query->get('type', 'soft'); // 'soft' par défaut
        
        if ($type === 'hard') {
            // Suppression définitive de la base de données
            $entityManager->remove($user);
        } else {
            // Suppression logique (désactivation)
            // Supposons que vous ayez une méthode setActive dans votre entité User
            if (method_exists($user, 'setActive')) {
                $user->setActive(false);
            }
            // Alternative avec un champ deletedAt
            if (method_exists($user, 'setDeletedAt')) {
                $user->setDeletedAt(new \DateTime());
            }
        }
        
        // Sauvegarde les modifications
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}

