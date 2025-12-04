<?php

namespace App\Repository;

use App\Entity\LignePanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LignePanier>
 *
 * @method LignePanier|null find($id, $lockMode = null, $lockVersion = null)
 * @method LignePanier|null findOneBy(array $criteria, array $orderBy = null)
 * @method LignePanier[]    findAll()
 * @method LignePanier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LignePanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LignePanier::class);
    }

    public function save(LignePanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LignePanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find cart item by cart and book
     */
    public function findByPanierAndLivre($panier, $livre): ?LignePanier
    {
        return $this->createQueryBuilder('lp')
            ->andWhere('lp.panier = :panier')
            ->andWhere('lp.livre = :livre')
            ->setParameter('panier', $panier)
            ->setParameter('livre', $livre)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
