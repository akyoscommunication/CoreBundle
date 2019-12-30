<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\MeniItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MeniItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeniItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeniItem[]    findAll()
 * @method MeniItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeniItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeniItem::class);
    }

    // /**
    //  * @return MeniItem[] Returns an array of MeniItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MeniItem
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
