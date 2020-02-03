<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\RgpdOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RgpdOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method RgpdOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method RgpdOptions[]    findAll()
 * @method RgpdOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RgpdOptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RgpdOptions::class);
    }

    // /**
    //  * @return RgpdOptions[] Returns an array of RgpdOptions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RgpdOptions
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
