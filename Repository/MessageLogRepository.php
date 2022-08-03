<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\MessageLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageLog[]    findAll()
 * @method MessageLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageLog::class);
    }

    // /**
    //  * @return MessageLog[] Returns an array of MessageLog objects
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
    public function findOneBySomeField($value): ?MessageLog
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
