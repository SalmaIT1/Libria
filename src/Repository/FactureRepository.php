<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function save(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCommande(Commande $commande): ?Facture
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.commande = :commande')
            ->setParameter('commande', $commande)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByNumero(string $numero): ?Facture
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.numero = :numero')
            ->setParameter('numero', $numero)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
