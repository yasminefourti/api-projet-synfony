<?php

namespace App\Repository;
use App\Entity\User;
use App\Entity\Objectif;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Enregistre une transaction.
     *
     * @param Transaction $entity
     * @param bool $flush
     * @return void
     */
    public function save(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une transaction.
     *
     * @param Transaction $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve toutes les transactions d'un objectif spécifique.
     *
     * @param int $objectifId
     * @return Transaction[]
     */
    public function findByObjectifId(int $objectifId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.objectif = :objectifId')
            ->setParameter('objectifId', $objectifId)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les transactions d'un objectif par type (dépense ou recette).
     *
     * @param int $objectifId
     * @param string $type
     * @return Transaction[]
     */
    public function findByObjectifIdAndType(int $objectifId, string $type): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.objectif = :objectifId')
            ->andWhere('t.type = :type')
            ->setParameter('objectifId', $objectifId)
            ->setParameter('type', $type)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les transactions effectuées entre deux dates pour un objectif spécifique.
     *
     * @param int $objectifId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Transaction[]
     */
    public function findByObjectifIdAndDateRange(int $objectifId, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.objectif = :objectifId')
            ->andWhere('t.date >= :startDate')
            ->andWhere('t.date <= :endDate')
            ->setParameter('objectifId', $objectifId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les transactions d'un utilisateur pour un objectif spécifique
     */
    public function findByUserAndObjectif(User $user, Objectif $objectif): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.objectif = :objectif')
            ->setParameter('user', $user)
            ->setParameter('objectif', $objectif)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculer le total des transactions pour un objectif
     */
    public function getTotalAmountByObjectif(User $user, Objectif $objectif): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->andWhere('t.user = :user')
            ->andWhere('t.objectif = :objectif')
            ->setParameter('user', $user)
            ->setParameter('objectif', $objectif)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Trouve les transactions par catégorie pour un objectif
     */
    public function findByObjectifAndCategorie(int $objectifId, int $categorieId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.objectif = :objectifId')
            ->andWhere('t.categorie = :categorieId')
            ->setParameter('objectifId', $objectifId)
            ->setParameter('categorieId', $categorieId)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les transactions par description (recherche partielle)
     */
    public function findByDescriptionLike(string $description): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.description LIKE :description')
            ->setParameter('description', '%' . $description . '%')
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des recettes pour un objectif
     */
    public function getTotalRecettesByObjectif(int $objectifId): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->andWhere('t.objectif = :objectifId')
            ->andWhere('t.type = :type')
            ->setParameter('objectifId', $objectifId)
            ->setParameter('type', 'recette')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Calcule le total des dépenses pour un objectif
     */
    public function getTotalDepensesByObjectif(int $objectifId): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as total')
            ->andWhere('t.objectif = :objectifId')
            ->andWhere('t.type = :type')
            ->setParameter('objectifId', $objectifId)
            ->setParameter('type', 'dépense')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}