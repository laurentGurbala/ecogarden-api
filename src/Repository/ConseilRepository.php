<?php

namespace App\Repository;

use App\Entity\Conseil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conseil>
 */
class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    /**
     * @return Conseil[] retourne un tableau de conseil du mois en cours
     */
    public function findByCurrentMonth(): array
    {
        $currentMonth = (int) date('n');

        return $this->createQueryBuilder('c')
            ->where('c.mois = :mois')
            ->setParameter('mois', $currentMonth)
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Conseil[] Returns an array of Conseil objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conseil
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
