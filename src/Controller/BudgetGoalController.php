<?php
// src/Controller/BudgetGoalController.php
namespace App\Controller;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/budget/goals', name: 'api_budget_goals_')]
class BudgetGoalController extends AbstractController
{
    // Récupérer tous les objectifs
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ObjectifRepository $objectifRepository): JsonResponse
    {
        // Récupère les objectifs de l'utilisateur connecté
        $objectifs = $objectifRepository->findBy(['user' => $this->getUser()]);
        
        return $this->json($objectifs, Response::HTTP_OK, [], ['groups' => 'goal:list']);
    }
    
    // Récupérer un objectif spécifique
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Objectif $objectif): JsonResponse
    {
        // Vérifie que l'objectif appartient à l'utilisateur connecté
        if ($objectif->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }
        
        return $this->json($objectif, Response::HTTP_OK, [], ['groups' => 'goal:detail']);
    }
    
    // Créer un objectif
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ObjectifRepository $objectifRepository
    ): JsonResponse {
        // Vérifier si l'utilisateur a déjà créé un objectif
        $existingGoal = $objectifRepository->findOneBy(['user' => $this->getUser()]);
        if ($existingGoal) {
            return $this->json([
                'message' => 'Vous avez déjà créé un objectif. Vous ne pouvez avoir qu\'un seul objectif actif.'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $data = $request->getContent();
        
        try {
            // Désérialise les données JSON en objet Objectif
            $objectif = $serializer->deserialize($data, Objectif::class, 'json');
            $objectif->setUser($this->getUser());
            
            // Initialiser le montant actuel à 0 par défaut
            if ($objectif->getCurrentAmount() === null) {
                $objectif->setCurrentAmount(0);
            }
            
            // Valide l'objet selon les contraintes définies
            $errors = $validator->validate($objectif);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }
            
            // Enregistre l'objectif en base de données
            $entityManager->persist($objectif);
            $entityManager->flush();
            
            return $this->json($objectif, Response::HTTP_CREATED, [], ['groups' => 'goal:detail']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    // Mettre à jour un objectif
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Objectif $objectif,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // Vérifie que l'objectif appartient à l'utilisateur connecté
        if ($objectif->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }
        
        $data = $request->getContent();
        
        try {
            // Désérialise en utilisant l'objet existant comme base
            $serializer->deserialize($data, Objectif::class, 'json', [
                'object_to_populate' => $objectif
            ]);
            
            // S'assure que l'utilisateur reste le même
            $objectif->setUser($this->getUser());
            
            // Valide les modifications
            $errors = $validator->validate($objectif);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }
            
            // Sauvegarde les modifications
            $entityManager->flush();
            
            return $this->json($objectif, Response::HTTP_OK, [], ['groups' => 'goal:detail']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    // Supprimer un objectif
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Objectif $objectif,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifie que l'objectif appartient à l'utilisateur connecté
        if ($objectif->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Accès non autorisé'], Response::HTTP_FORBIDDEN);
        }
        
        // Supprime l'objectif
        $entityManager->remove($objectif);
        $entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}