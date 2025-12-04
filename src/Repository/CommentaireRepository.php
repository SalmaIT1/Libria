<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Get average rating for a book
     */
    public function getAverageRating(int $livreId): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.rating) as avgRating')
            ->where('c.livre = :livreId')
            ->setParameter('livreId', $livreId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Get comment count for a book
     */
    public function getCommentCount(int $livreId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.livre = :livreId')
            ->setParameter('livreId', $livreId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

