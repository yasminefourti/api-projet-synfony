<?php

namespace App\Repository;

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
}