<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\OptionCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OptionCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionCategory[]    findAll()
 * @method OptionCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionCategory::class);
    }

    // /**
    //  * @return OptionCategory[] Returns an array of OptionCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OptionCategory
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
