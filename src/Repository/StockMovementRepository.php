<?php

namespace App\Repository;

use App\Entity\StockMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockMovement>
 */
class StockMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMovement::class);
    }

    public function save(StockMovement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StockMovement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByLivre($livre, $limit = 50): array
    {
        return $this->createQueryBuilder('sm')
            ->where('sm.livre = :livre')
            ->setParameter('livre', $livre)
            ->orderBy('sm.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLowStockBooks(int $threshold = 5): array
    {
        return $this->createQueryBuilder('sm')
            ->select('l.id, l.titre, l.nbExemplaires as currentStock, COUNT(sm.id) as movementCount')
            ->from('App\Entity\Livre', 'l')
            ->leftJoin('App\Entity\StockMovement', 'sm', 'WITH', 'sm.livre = l.id')
            ->where('l.nbExemplaires <= :threshold')
            ->setParameter('threshold', $threshold)
            ->groupBy('l.id, l.titre, l.nbExemplaires')
            ->orderBy('l.nbExemplaires', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMovementsByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('sm')
            ->where('sm.createdAt BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getStockStats(): array
    {
        $qb = $this->createQueryBuilder('sm')
            ->select('COUNT(sm.id) as totalMovements')
            ->addSelect('SUM(CASE WHEN sm.type = :increase THEN sm.quantity ELSE 0 END) as totalIncreases')
            ->addSelect('SUM(CASE WHEN sm.type = :decrease THEN sm.quantity ELSE 0 END) as totalDecreases')
            ->addSelect('SUM(CASE WHEN sm.type = :sale THEN sm.quantity ELSE 0 END) as totalSales')
            ->setParameter('increase', StockMovement::TYPE_INCREASE)
            ->setParameter('decrease', StockMovement::TYPE_DECREASE)
            ->setParameter('sale', StockMovement::TYPE_SALE);

        return $qb->getQuery()->getSingleResult();
    }

    public function getTopMovingBooks(int $limit = 10): array
    {
        return $this->createQueryBuilder('sm')
            ->select('l.id, l.titre, COUNT(sm.id) as movementCount, SUM(sm.quantity) as totalQuantity')
            ->from('App\Entity\Livre', 'l')
            ->join('App\Entity\StockMovement', 'sm', 'WITH', 'sm.livre = l.id')
            ->where('sm.createdAt >= :date')
            ->setParameter('date', new \DateTime('-30 days'))
            ->groupBy('l.id, l.titre')
            ->orderBy('movementCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getRecentMovements(int $limit = 20): array
    {
        return $this->createQueryBuilder('sm')
            ->leftJoin('sm.livre', 'l')
            ->leftJoin('sm.user', 'u')
            ->addSelect('l', 'u')
            ->orderBy('sm.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
