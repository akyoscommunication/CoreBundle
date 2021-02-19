<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\CoreOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CoreOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoreOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoreOptions[]    findAll()
 * @method CoreOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoreOptionsRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CoreOptions::class);
	}
	
	// /**
	//  * @return CoreOptions[] Returns an array of CoreOptions objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('c.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?CoreOptions
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
