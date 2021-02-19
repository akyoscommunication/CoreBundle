<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\CustomFieldsGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CustomFieldsGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomFieldsGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomFieldsGroup[]    findAll()
 * @method CustomFieldsGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomFieldsGroupRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CustomFieldsGroup::class);
	}

	// /**
	//  * @return CustomFieldsGroup[] Returns an array of CustomFieldsGroup objects
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
	public function findOneBySomeField($value): ?CustomFieldsGroup
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
