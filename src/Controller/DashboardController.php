<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/budget')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TransactionRepository $transactionRepository,
        private ObjectifRepository $objectifRepository
    ) {}

    #[Route('/goals/{id}/progress', name: 'api_budget_goal_progress', methods: ['GET'])]
    public function getBudgetGoalProgress(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer l'objectif spécifique de l'utilisateur
        $objectif = $this->objectifRepository->find($id);
        
        // Vérifier que l'objectif existe et appartient à l'utilisateur connecté
        if (!$objectif || $objectif->getUser() !== $user) {
            return new JsonResponse(['error' => 'Objectif non trouvé ou accès non autorisé'], 404);
        }

        // Récupérer les transactions liées à cet objectif
        $transactions = $this->transactionRepository->findByUserAndObjectif($user, $objectif);
        
        // Utiliser le current_amount de la table objectif au lieu de calculer
        $currentAmount = (float)$objectif->getCurrentAmount();
        $targetAmount = (float)$objectif->getTargetAmount();
        
        // Calculer la progression
        $progression = $targetAmount > 0 ? ($currentAmount / $targetAmount) * 100 : 0;

        // Calculer les jours restants
        $daysRemaining = 0;
        if ($objectif->getEndDate()) {
            $now = new \DateTime();
            if ($objectif->getEndDate() > $now) {
                $daysRemaining = $now->diff($objectif->getEndDate())->days;
            }
        }

        $data = [
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'progression' => min(100, round($progression, 2)), // Limiter à 100% et arrondir
            'daysRemaining' => $daysRemaining,
            'objectif' => [
                'id' => $objectif->getId(),
                'title' => $objectif->getTitle(),
                'startDate' => $objectif->getStartDate()?->format('Y-m-d'),
                'endDate' => $objectif->getEndDate()?->format('Y-m-d')
            ]
        ];

        return new JsonResponse($data);
    }

    #[Route('/dashboard', name: 'api_budget_dashboard', methods: ['GET'])]
    public function getDashboard(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer l'objectif unique de l'utilisateur
        $objectif = $this->objectifRepository->findOneBy(['user' => $user]);
        
        if (!$objectif) {
            return new JsonResponse([
                'objectifs' => [],
                'statistiques' => [
                    'hasObjectif' => false,
                    'totalTransactions' => 0,
                    'progression' => 0,
                    'status' => 'aucun_objectif'
                ],
                'message' => 'Vue globale : aucun objectif créé'
            ]);
        }
        
        // Récupérer toutes les transactions pour cet objectif
        $transactions = $this->transactionRepository->findByUserAndObjectif($user, $objectif);
        
        // Utiliser les valeurs de la table objectif
        $currentAmount = (float)$objectif->getCurrentAmount();
        $targetAmount = (float)$objectif->getTargetAmount();
        
        // Calculer la progression
        $progression = $targetAmount > 0 ? ($currentAmount / $targetAmount) * 100 : 0;
        
        // Calculer les jours restants
        $now = new \DateTime();
        $daysRemaining = 0;
        if ($objectif->getEndDate() && $objectif->getEndDate() > $now) {
            $daysRemaining = $now->diff($objectif->getEndDate())->days;
        }
        
        // Déterminer le statut
        $status = $this->getObjectifStatus($progression, $daysRemaining, $objectif->getEndDate());
        
        // Calculer les statistiques des transactions par mois (pour les graphiques)
        $monthlyStats = $this->getMonthlyTransactionStats($transactions);
        
        // Données de l'objectif unique
        $objectifData = [
            'id' => $objectif->getId(),
            'title' => $objectif->getTitle(),
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'progression' => min(100, round($progression, 2)),
            'daysRemaining' => $daysRemaining,
            'status' => $status,
            'startDate' => $objectif->getStartDate()?->format('Y-m-d'),
            'endDate' => $objectif->getEndDate()?->format('Y-m-d'),
            'transactionsCount' => count($transactions),
            'recentTransactions' => $this->getRecentTransactions($transactions, 5)
        ];
        
        // Statistiques globales
        $globalStats = [
            'hasObjectif' => true,
            'totalTransactions' => count($transactions),
            'totalAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'progression' => round($progression, 2),
            'status' => $status,
            'daysRemaining' => $daysRemaining,
            'isCompleted' => $progression >= 100,
            'isExpired' => $daysRemaining <= 0 && $objectif->getEndDate() !== null,
            'averageTransactionAmount' => count($transactions) > 0 ? $currentAmount / count($transactions) : 0,
            'monthlyStats' => $monthlyStats
        ];
        
        return new JsonResponse([
            'objectifs' => [$objectifData], // Garder le format tableau pour cohérence avec l'API
            'objectif' => $objectifData, // Objectif unique accessible directement
            'statistiques' => $globalStats,
            'message' => 'Vue globale : objectif unique + état'
        ]);
    }

    #[Route('/goals/{id}/update-amount', name: 'api_budget_update_amount', methods: ['PATCH'])]
    public function updateCurrentAmount(int $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $objectif = $this->objectifRepository->find($id);
        
        if (!$objectif || $objectif->getUser() !== $user) {
            return new JsonResponse(['error' => 'Objectif non trouvé ou accès non autorisé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['amount']) || !is_numeric($data['amount'])) {
            return new JsonResponse(['error' => 'Montant invalide'], 400);
        }

        $newAmount = (float)$data['amount'];
        
        // Vérifier que le montant n'est pas négatif
        if ($newAmount < 0) {
            return new JsonResponse(['error' => 'Le montant ne peut pas être négatif'], 400);
        }

        // Mettre à jour le current_amount
        $objectif->setCurrentAmount($newAmount);
        $this->entityManager->flush();

        // Recalculer la progression
        $targetAmount = (float)$objectif->getTargetAmount();
        $progression = $targetAmount > 0 ? ($newAmount / $targetAmount) * 100 : 0;

        return new JsonResponse([
            'success' => true,
            'currentAmount' => $newAmount,
            'targetAmount' => $targetAmount,
            'progression' => min(100, round($progression, 2)),
            'message' => 'Montant mis à jour avec succès'
        ]);
    }

    private function getObjectifStatus(float $progression, int $daysRemaining, ?\DateTimeInterface $endDate): string
    {
        // Si l'objectif est atteint
        if ($progression >= 100) {
            return 'terminé';
        }
        
        // Si la date de fin est passée
        if ($endDate && $daysRemaining <= 0) {
            return 'expiré';
        }
        
        // Si pas de date de fin définie
        if (!$endDate) {
            if ($progression > 75) return 'avancé';
            if ($progression > 25) return 'en_cours';
            if ($progression > 0) return 'commencé';
            return 'non_commencé';
        }
        
        // Avec date de fin
        if ($daysRemaining <= 7 && $progression < 90) {
            return 'urgent';
        }
        
        if ($progression > 75) {
            return 'proche_objectif';
        }
        
        if ($progression > 25) {
            return 'en_cours';
        }
        
        if ($progression > 0) {
            return 'commencé';
        }
        
        return 'non_commencé';
    }
    
    private function getMonthlyTransactionStats(array $transactions): array
    {
        $monthlyData = [];
        
        foreach ($transactions as $transaction) {
            $month = $transaction->getDate()->format('Y-m');
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'month' => $month,
                    'recettes' => 0,
                    'depenses' => 0,
                    'net' => 0,
                    'count' => 0
                ];
            }
            
            $amount = (float)$transaction->getAmount();
            $type = $transaction->getType();
            
            if ($type === 'recette') {
                $monthlyData[$month]['recettes'] += $amount;
                $monthlyData[$month]['net'] += $amount;
            } elseif ($type === 'dépense') {
                $monthlyData[$month]['depenses'] += $amount;
                $monthlyData[$month]['net'] -= $amount;
            }
            
            $monthlyData[$month]['count']++;
        }
        
        // Trier par mois décroissant
        krsort($monthlyData);
        
        return array_values($monthlyData);
    }
    
    private function getRecentTransactions(array $transactions, int $limit): array
    {
        // Trier les transactions par date décroissante
        usort($transactions, fn($a, $b) => $b->getDate() <=> $a->getDate());
        
        $recentTransactions = [];
        $count = 0;
        
        foreach ($transactions as $transaction) {
            if ($count >= $limit) break;
            
            $recentTransactions[] = [
                'id' => $transaction->getId(),
                'amount' => (float)$transaction->getAmount(),
                'type' => $transaction->getType(),
                'description' => $transaction->getDescription(),
                'date' => $transaction->getDate()->format('Y-m-d H:i:s'),
                'categorie' => $transaction->getCategorie() ? [
                    'id' => $transaction->getCategorie()->getId(),
                    'nom' => $transaction->getCategorie()->getNom()
                ] : null
            ];
            $count++;
        }
        
        return $recentTransactions;
    }

    #[Route('/transactions/summary', name: 'api_budget_transactions_summary', methods: ['GET'])]
    public function getTransactionsSummary(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $objectif = $this->objectifRepository->findOneBy(['user' => $user]);
        
        if (!$objectif) {
            return new JsonResponse([
                'error' => 'Aucun objectif trouvé'
            ], 404);
        }

        $transactions = $this->transactionRepository->findByUserAndObjectif($user, $objectif);
        
        $totalRecettes = 0;
        $totalDepenses = 0;
        $countRecettes = 0;
        $countDepenses = 0;

        foreach ($transactions as $transaction) {
            $amount = (float)$transaction->getAmount();
            if ($transaction->getType() === 'recette') {
                $totalRecettes += $amount;
                $countRecettes++;
            } elseif ($transaction->getType() === 'dépense') {
                $totalDepenses += $amount;
                $countDepenses++;
            }
        }

        return new JsonResponse([
            'summary' => [
                'totalRecettes' => $totalRecettes,
                'totalDepenses' => $totalDepenses,
                'balance' => $totalRecettes - $totalDepenses,
                'countRecettes' => $countRecettes,
                'countDepenses' => $countDepenses,
                'totalTransactions' => count($transactions),
                'averageRecette' => $countRecettes > 0 ? $totalRecettes / $countRecettes : 0,
                'averageDepense' => $countDepenses > 0 ? $totalDepenses / $countDepenses : 0
            ],
            'monthlyStats' => $this->getMonthlyTransactionStats($transactions)
        ]);
    }
}