<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\AdminAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdminAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminAccess[]    findAll()
 * @method AdminAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminAccessRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AdminAccess::class);
	}
	
	public function searchByName($keyword = null)
	{
		return $this->createQueryBuilder('a')
			->andWhere('a.name LIKE :keyword')
			->setParameter('keyword', '%' . $keyword . '%')
			->getQuery()
			->getResult();
	}
	// /**
	//  * @return AdminAccess[] Returns an array of AdminAccess objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('a')
			->andWhere('a.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('a.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/
	
	/*
	public function findOneBySomeField($value): ?AdminAccess
	{
		return $this->createQueryBuilder('a')
			->andWhere('a.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
