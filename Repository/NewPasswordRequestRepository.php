<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\NewPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewPasswordRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewPasswordRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewPasswordRequest[]    findAll()
 * @method NewPasswordRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewPasswordRequestRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, NewPasswordRequest::class);
	}

	// /**
	//  * @return NewPasswordRequest[] Returns an array of NewPasswordRequest objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('n')
			->andWhere('n.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('n.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?NewPasswordRequest
	{
		return $this->createQueryBuilder('n')
			->andWhere('n.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
