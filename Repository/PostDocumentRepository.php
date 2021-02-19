<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\PostDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostDocument[]    findAll()
 * @method PostDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostDocumentRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PostDocument::class);
	}

	// /**
	//  * @return PostDocument[] Returns an array of PostDocument objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('p.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?PostDocument
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
