<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\Redirect301;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Redirect301|null find($id, $lockMode = null, $lockVersion = null)
 * @method Redirect301|null findOneBy(array $criteria, array $orderBy = null)
 * @method Redirect301[]    findAll()
 * @method Redirect301[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Redirect301Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirect301::class);
    }

    // /**
    //  * @return Redirect301[] Returns an array of Redirect301 objects
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
    public function findOneBySomeField($value): ?Redirect301
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
