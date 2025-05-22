<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/budget')]
class CategorieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private CategorieRepository $categorieRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        CategorieRepository $categorieRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Récupère la liste des catégories disponibles pour l'utilisateur connecté
     */
    #[Route('/categories', name: 'api_categories_list', methods: ['GET'])]
    public function getAllCategories(): JsonResponse
    {
        $user = $this->getUser();
        $categories = $this->categorieRepository->findByUser($user);

        return $this->json(
            $categories,
            Response::HTTP_OK,
            [],
            ['groups' => 'categorie:read']
        );
    }

    /**
     * Crée une catégorie perso
     */
    #[Route('/categories', name: 'api_categories_create', methods: ['POST'])]
    public function createCategorie(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['nom']) || empty($data['nom'])) {
            return $this->json(['message' => 'Le nom de la catégorie est obligatoire'], Response::HTTP_BAD_REQUEST);
        }
        
        $categorie = new Categorie();
        $categorie->setNom($data['nom']);
        
        if (isset($data['description'])) {
            $categorie->setDescription($data['description']);
        }
        
        $categorie->setUser($this->getUser());
        
        $this->entityManager->persist($categorie);
        $this->entityManager->flush();
        
        return $this->json(
            $categorie,
            Response::HTTP_CREATED,
            [],
            ['groups' => 'categorie:read']
        );
    }

    /**
     * Met à jour une catégorie
     */
    #[Route('/categories/{id}', name: 'api_categories_update', methods: ['PUT'])]
    public function updateCategorie(int $id, Request $request): JsonResponse
    {
        $categorie = $this->categorieRepository->find($id);
        
        if (!$categorie) {
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier que l'utilisateur est bien le propriétaire de la catégorie
        if ($categorie->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Accès non autorisé à cette catégorie'], Response::HTTP_FORBIDDEN);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['nom']) && !empty($data['nom'])) {
            $categorie->setNom($data['nom']);
        }
        
        if (isset($data['description'])) {
            $categorie->setDescription($data['description']);
        }
        
        $this->entityManager->flush();
        
        return $this->json(
            $categorie,
            Response::HTTP_OK,
            [],
            ['groups' => 'categorie:read']
        );
    }

    /**
     * Supprime une catégorie
     */
    #[Route('/categories/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function deleteCategorie(int $id): JsonResponse
    {
        $categorie = $this->categorieRepository->find($id);
        
        if (!$categorie) {
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier que l'utilisateur est bien le propriétaire de la catégorie
        if ($categorie->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Accès non autorisé à cette catégorie'], Response::HTTP_FORBIDDEN);
        }
        
        // Vérifier s'il y a des transactions liées à cette catégorie
        if (!$categorie->getTransactions()->isEmpty()) {
            return $this->json(
                ['message' => 'Impossible de supprimer cette catégorie car elle est utilisée par des transactions'],
                Response::HTTP_BAD_REQUEST
            );
        }
        
        $this->entityManager->remove($categorie);
        $this->entityManager->flush();
        
        return $this->json(['message' => 'Catégorie supprimée avec succès'], Response::HTTP_OK);
    }
}