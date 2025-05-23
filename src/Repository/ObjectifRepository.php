<?php

namespace App\Repository;

use App\Entity\Objectif;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objectif>
 *
 * @method Objectif|null find($id, $lockMode = null, $lockVersion = null)
 * @method Objectif|null findOneBy(array $criteria, array $orderBy = null)
 * @method Objectif[]    findAll()
 * @method Objectif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objectif::class);
    }

    /**
     * Enregistre une entité dans la base de données
     */
    public function save(Objectif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une entité de la base de données
     */
    public function remove(Objectif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve l'objectif d'un utilisateur spécifique
     */
    public function findOneByUser(User $user): ?Objectif
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Récupérer l'objectif unique d'un utilisateur
     */
    public function findUserObjectif(User $user): ?Objectif
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Vérifier si un utilisateur a déjà un objectif
     */
    public function userHasObjectif(User $user): bool
    {
        return $this->count(['user' => $user]) > 0;
    }
}