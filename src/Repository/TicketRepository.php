<?php

namespace App\Repository;

use App\Entity\Ticket;
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

    /**
     * Toutes les conversations racines (parent IS NULL), ordonnées par date desc.
     *
     * @return Ticket[]
     */
    public function findRootTickets(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.parent IS NULL')
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
        return $this->createQueryBuilder('t')
            ->andWhere('t.parent = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
