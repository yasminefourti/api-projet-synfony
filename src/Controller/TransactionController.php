<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Objectif;
use App\Entity\Transaction;
use App\Entity\Categorie;
use App\Repository\ObjectifRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TransactionController extends AbstractController
{
    private $entityManager;
    private $transactionRepository;
    private $objectifRepository;
    private $serializer;
    private $validator;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        TransactionRepository $transactionRepository,
        ObjectifRepository $objectifRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->transactionRepository = $transactionRepository;
        $this->objectifRepository = $objectifRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->security = $security;
    }

    /**
     * Liste toutes les transactions d'un objectif
     * 
     * @Route("/api/budget/goals/{goalId}/transactions", name="api_goal_transactions_list", methods={"GET"})
     */
    #[Route('/api/budget/goals/{goalId}/transactions', name: 'api_goal_transactions_list', methods: ['GET'])]
    public function listTransactions(int $goalId): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Vous devez être connecté pour accéder à cette ressource'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier que l'objectif existe et appartient à l'utilisateur
        $objectif = $this->objectifRepository->find($goalId);
        if (!$objectif) {
            return $this->json(['message' => 'Objectif non trouvé'], Response::HTTP_NOT_FOUND);
        }

        

        // Debug des IDs pour vérifier la comparaison
        $userId = $user->getId();
        $objectifUserId = $objectif->getUser()->getId();
        
        // Comparaison stricte des ID d'utilisateur après conversion en string pour éviter les problèmes de types
        if ((string)$objectifUserId !== (string)$userId) {
            return $this->json([
                'message' => 'Vous n\'êtes pas autorisé à accéder à cet objectif',
                'debug' => [
                    'user_id' => $userId,
                    'objectif_user_id' => $objectifUserId
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        // Récupérer toutes les transactions de l'objectif
        $transactions = $objectif->getTransactions();

        return $this->json(
            $transactions,
            Response::HTTP_OK,
            [],
            ['groups' => 'transaction:read']
        );
    }

    /**
     * Ajoute une nouvelle transaction à un objectif
     * 
     * @Route("/api/budget/goals/{goalId}/transactions", name="api_goal_transaction_create", methods={"POST"})
     */
    #[Route('/api/budget/goals/{goalId}/transactions', name: 'api_goal_transaction_create', methods: ['POST'])]
    public function createTransaction(Request $request, int $goalId): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Vous devez être connecté pour accéder à cette ressource'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier que l'objectif existe et appartient à l'utilisateur
        $objectif = $this->objectifRepository->find($goalId);
        if (!$objectif) {
            return $this->json(['message' => 'Objectif non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($objectif->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Vous n\'êtes pas autorisé à accéder à cet objectif'], Response::HTTP_FORBIDDEN);
        }

        // Désérialiser les données de la requête
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setObjectif($objectif);
        
        // Définir les propriétés de la transaction
        if (isset($data['type'])) {
            $transaction->setType($data['type']);
        }
        
        if (isset($data['amount'])) {
            $transaction->setAmount($data['amount']);
            
            // Mettre à jour le montant actuel de l'objectif
            $currentAmount = $objectif->getCurrentAmount();
            if ($data['type'] === 'recette') {
                $objectif->setCurrentAmount($currentAmount + $data['amount']);
            } elseif ($data['type'] === 'dépense') {
                $objectif->setCurrentAmount($currentAmount - $data['amount']);
            }
        }
        
        if (isset($data['date'])) {
            $transaction->setDate(new \DateTime($data['date']));
        } else {
            $transaction->setDate(new \DateTime());
        }
        
        if (isset($data['description'])) {
            $transaction->setDescription($data['description']);
        }

        // Gérer la catégorie
        if (isset($data['categorie_id'])) {
            $categorie = $this->entityManager->getRepository(Categorie::class)->find($data['categorie_id']);
            if ($categorie) {
                // Vérifier que la catégorie appartient bien à l'utilisateur
                if ($categorie->getUser()->getId() === $user->getId()) {
                    $transaction->setCategorie($categorie);
                } else {
                    return $this->json(['message' => 'Vous n\'êtes pas autorisé à utiliser cette catégorie'], Response::HTTP_FORBIDDEN);
                }
            } else {
                return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
            }
        }

        // Valider la transaction
        $errors = $this->validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['message' => 'Validation failed', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrer la transaction et mettre à jour l'objectif
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $this->json(
            $transaction,
            Response::HTTP_CREATED,
            [],
            ['groups' => 'transaction:read']
        );
    }

    /**
     * Récupère les détails d'une transaction
     * 
     * @Route("/api/budget/transactions/{txId}", name="api_transaction_show", methods={"GET"})
     */
    #[Route('/api/budget/transactions/{txId}', name: 'api_transaction_show', methods: ['GET'])]
    public function showTransaction(int $txId): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Vous devez être connecté pour accéder à cette ressource'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer la transaction
        $transaction = $this->transactionRepository->find($txId);
        if (!$transaction) {
            return $this->json(['message' => 'Transaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la transaction appartient à un objectif de l'utilisateur
        $objectif = $transaction->getObjectif();
        $userId = $user->getId();
        $objectifUserId = $objectif->getUser()->getId();
        
        // Comparaison stricte des ID d'utilisateur après conversion en string
        if ((string)$objectifUserId !== (string)$userId) {
            return $this->json([
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette transaction',
                'debug' => [
                    'user_id' => $userId,
                    'objectif_user_id' => $objectifUserId
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            $transaction,
            Response::HTTP_OK,
            [],
            ['groups' => 'transaction:read']
        );
    }

    /**
     * Modifie une transaction existante
     * 
     * @Route("/api/budget/transactions/{txId}", name="api_transaction_update", methods={"PUT"})
     */
    #[Route('/api/budget/transactions/{txId}', name: 'api_transaction_update', methods: ['PUT'])]
    public function updateTransaction(Request $request, int $txId): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Vous devez être connecté pour accéder à cette ressource'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer la transaction
        $transaction = $this->transactionRepository->find($txId);
        if (!$transaction) {
            return $this->json(['message' => 'Transaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la transaction appartient à un objectif de l'utilisateur
        $objectif = $transaction->getObjectif();
        if ($objectif->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Vous n\'êtes pas autorisé à modifier cette transaction'], Response::HTTP_FORBIDDEN);
        }

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Sauvegarder l'ancien montant et type pour ajuster le montant de l'objectif
        $oldAmount = $transaction->getAmount();
        $oldType = $transaction->getType();
        
        // Mettre à jour les propriétés de la transaction
        if (isset($data['type'])) {
            $transaction->setType($data['type']);
        }
        
        if (isset($data['amount'])) {
            $transaction->setAmount($data['amount']);
        }
        
        if (isset($data['date'])) {
            $transaction->setDate(new \DateTime($data['date']));
        }
        
        if (isset($data['description'])) {
            $transaction->setDescription($data['description']);
        }

        // Gérer la catégorie dans la mise à jour
        if (isset($data['categorie_id'])) {
            if ($data['categorie_id'] === null) {
                // Supprimer la catégorie
                $transaction->setCategorie(null);
            } else {
                $categorie = $this->entityManager->getRepository(Categorie::class)->find($data['categorie_id']);
                if ($categorie) {
                    // Vérifier que la catégorie appartient bien à l'utilisateur
                    if ($categorie->getUser()->getId() === $user->getId()) {
                        $transaction->setCategorie($categorie);
                    } else {
                        return $this->json(['message' => 'Vous n\'êtes pas autorisé à utiliser cette catégorie'], Response::HTTP_FORBIDDEN);
                    }
                } else {
                    return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
                }
            }
        }

        // Valider la transaction
        $errors = $this->validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['message' => 'Validation failed', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Ajuster le montant actuel de l'objectif
        $currentAmount = $objectif->getCurrentAmount();
        
        // Annuler l'effet de l'ancienne transaction
        if ($oldType === 'recette') {
            $currentAmount -= $oldAmount;
        } elseif ($oldType === 'dépense') {
            $currentAmount += $oldAmount;
        }
        
        // Appliquer l'effet de la nouvelle transaction
        $newAmount = $transaction->getAmount();
        $newType = $transaction->getType();
        
        if ($newType === 'recette') {
            $currentAmount += $newAmount;
        } elseif ($newType === 'dépense') {
            $currentAmount -= $newAmount;
        }
        
        $objectif->setCurrentAmount($currentAmount);

        // Enregistrer les modifications
        $this->entityManager->flush();

        return $this->json(
            $transaction,
            Response::HTTP_OK,
            [],
            ['groups' => 'transaction:read']
        );
    }

    /**
     * Supprime une transaction existante
     * 
     * @Route("/api/budget/transactions/{txId}", name="api_transaction_delete", methods={"DELETE"})
     */
    #[Route('/api/budget/transactions/{txId}', name: 'api_transaction_delete', methods: ['DELETE'])]
    public function deleteTransaction(int $txId): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Vous devez être connecté pour accéder à cette ressource'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer la transaction
        $transaction = $this->transactionRepository->find($txId);
        if (!$transaction) {
            return $this->json(['message' => 'Transaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la transaction appartient à un objectif de l'utilisateur
        $objectif = $transaction->getObjectif();
        if ($objectif->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette transaction'], Response::HTTP_FORBIDDEN);
        }

        // Ajuster le montant de l'objectif avant de supprimer la transaction
        $currentAmount = $objectif->getCurrentAmount();
        $transactionAmount = $transaction->getAmount();
        $transactionType = $transaction->getType();
        
        if ($transactionType === 'recette') {
            $objectif->setCurrentAmount($currentAmount - $transactionAmount);
        } elseif ($transactionType === 'dépense') {
            $objectif->setCurrentAmount($currentAmount + $transactionAmount);
        }

        // Supprimer la transaction
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();

        return $this->json(['message' => 'Transaction supprimée avec succès'], Response::HTTP_OK);
    }
}