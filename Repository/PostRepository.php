<?php

namespace Akyos\CoreBundle\Repository;

use Akyos\CoreBundle\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}

    /**
     * @param null $keyword
     * @return int|mixed|string
     */
	public function searchByTitle($keyword = null)
	{
		return $this->createQueryBuilder('m')
			->andWhere('m.title LIKE :keyword')
			->setParameter('keyword', '%' . $keyword . '%')
			->orderBy('m.position')
			->getQuery()
			->getResult();
	}

    /**
     * @param $categoryObject
     * @param null $keyword
     * @return int|mixed|string
     */
	public function searchByCategory($categoryObject, $keyword = null)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.title LIKE :keyword')
			->andWhere('p = :category')
			->setParameter('keyword', '%' . $keyword . '%')
			->setParameter('category', $categoryObject)
			->orderBy('p.position')
			->getQuery()
			->getResult();
	}

    /**
     * @param $cat
     * @return Query
     */
    public function findByCategory($cat): Query
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.published = true');

        if ($cat) {
            $query = $query
                ->innerJoin('p.postCategories', 'pc')
                ->andWhere('pc.slug LIKE :cat')
                ->setParameter('cat', $cat);
        }

        return $query
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

	// /**
	//  * @return Post[] Returns an array of Post objects
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
	public function findOneBySomeField($value): ?Post
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
