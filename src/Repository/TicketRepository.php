<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    private function createActiveQueryBuilder(string $alias = 't')
    {
        return $this->createQueryBuilder($alias)
            ->andWhere(sprintf('%s.deleted = false', $alias));
    }

    private function createDeletedQueryBuilder(string $alias = 't')
    {
        return $this->createQueryBuilder($alias)
            ->andWhere(sprintf('%s.deleted = true', $alias));
    }

    /**
     * Toutes les conversations racines (parent IS NULL), ordonnées par date desc.
     *
     * @return Ticket[]
     */
    public function findRootTickets(): array
    {
        return $this->createActiveQueryBuilder('t')
            ->andWhere('t.parent IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Conversations racines créées par un utilisateur donné.
     *
     * @return Ticket[]
     */
    public function findRootTicketsByCreator(User $user): array
    {
        return $this->createActiveQueryBuilder('t')
            ->andWhere('t.parent IS NULL')
            ->andWhere('t.createdby = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les tickets (pas les réponses) créés par un utilisateur donné.
     *
     * @return Ticket[]
     */
    public function findAllByCreator(User $user): array
    {
        return $this->createActiveQueryBuilder('t')
            ->andWhere('t.parent IS NULL')
            ->andWhere('t.createdby = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les réponses d'un ticket racine, ordonnées chronologiquement.
     *
     * @return Ticket[]
     */
    public function findRepliesOf(Ticket $parent): array
    {
        return $this->createActiveQueryBuilder('t')
            ->andWhere('t.parent = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveTickets(): array
    {
        return $this->createActiveQueryBuilder('t')
            ->getQuery()
            ->getResult();
    }

    public function findDeletedTickets(): array
    {
        return $this->createDeletedQueryBuilder('t')
            ->andWhere('t.parent IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
