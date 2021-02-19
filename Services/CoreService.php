<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\CustomFieldValue;
use Akyos\CoreBundle\Repository\CustomFieldRepository;
use Akyos\CoreBundle\Repository\CustomFieldValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CoreService
{
	private $em;
	private $customFieldValueRepository;
	private $customFieldRepository;
	
	public function __construct(EntityManagerInterface $em, CustomFieldValueRepository $customFieldValueRepository,
								CustomFieldRepository $customFieldRepository)
	{
		$this->em = $em;
		$this->customFieldValueRepository = $customFieldValueRepository;
		$this->customFieldRepository = $customFieldRepository;
	}
	
	public function checkIfSingleEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasSingleEntities())) {
				return true;
			} else return false;
		} else return false;
	}
	
	public function checkIfArchiveEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasArchiveEntities())) {
				return true;
			} else return false;
		} else return false;
	}
	
	public function checkIfSeoEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasSeoEntities())) {
				return true;
			} else return false;
		} else return false;
	}
	
	public function checkIfBundleEnable($bundle, $options, $entity)
	{
		if (class_exists($bundle)) {
			$opt = $this->em->getRepository($options)->findAll();
			if ($opt) {
				if (in_array($entity, $opt[0]->getHasBuilderEntities())) {
					return true;
				} else return false;
			} else return false;
		} else return false;
	}
	
	public function getEntityAndFullString(string $entitySlug) {
		$entityFullName = null;
		$entity = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			try {
				$constant_reflex = new \ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
				$constant_value = $constant_reflex->getValue();
			} catch (\ReflectionException $e) {
				$constant_value = null;
			}
			if(null !== $constant_value) {
				if($m->getName()::ENTITY_SLUG === $entitySlug) {
					$entityFullName = $m->getName();
					$entity = array_reverse(explode('\\', $entityFullName))[0];
				}
			}
		}
		
		return [
			$entityFullName,
			$entity
		];
	}
	public function getCustomField(string $customFieldSlug, $object)
	{
		$customField = $this->customFieldRepository->findOneBy(['slug' => $customFieldSlug]);
		if(!$customField) {
			return null;
		}
		
		$customFieldValue = $this->customFieldValueRepository->findOneBy(['customField' => $customField, 'objectId' => $object->getId()]);
		if(!$customFieldValue) {
			return null;
		}
		
		switch ($customField->getType()) {
			case 'entity':
				if($customFieldValue->getValue()) {
					$customFieldValue = $this->em->getRepository($customField->getEntity())->find($customFieldValue->getValue());
				} else {
					$customFieldValue = null;
				}
				break;
			default:
				$customFieldValue = $customFieldValue->getValue();
				break;
		}
		
		return $customFieldValue;
	}
	
	public function setCustomField(string $customFieldSlug, $object, $value)
	{
		$customField = $this->customFieldRepository->findOneBy(['slug' => $customFieldSlug]);
		if(!$customField) {
			return null;
		}
		
		$customFieldValue = $this->customFieldValueRepository->findOneBy(['customField' => $customField, 'objectId' => $object->getId()]);
		if(!$customFieldValue) {
			return null;
		}
		
		switch ($customField->getType()) {
			case 'entity':
				$customFieldValue->setValue($value->getId());
				break;
			default:
				$customFieldValue->setValue($value);
				break;
		}
		return true;
	}
	
	/**
	 * @param array $customFieldCriterias
	 * @param string $entity
	 * array['fields']
	 *      [fieldName]
	 *          ['slug']
	 *          ['operator']
	 *          ['value']
	 * @param array|null $criterias
	 * @param array|null $orders
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param bool $query
	 * @return \Doctrine\ORM\Query|int|mixed|string
	 */
	public function searchByCustomField(array $customFieldCriterias, string $entity, ?array $criterias = null, ?array $orders = null, ?int $limit = null, ?int $offset = null, $query = false)
	{
		$customFieldValuesQuery = $this->customFieldValueRepository->createQueryBuilder('cfv')
			->innerJoin('cfv.customField', 'cf')
		;
		
		foreach ($customFieldCriterias as $customField) {
			if (!isset($customField['slug'])) return "Il manque l'entrée slug de tableau.";
			if (!isset($customField['operator'])) return "Il manque l'entrée operator de tableau.";
			if (!isset($customField['value'])) return "Il manque l'entrée value de tableau.";
			
			$slug = $customField['slug'];
			$operator = $customField['operator'];
			$value = $customField['value'];
			
			switch ($operator) {
				case 'IN':
					$customFieldValuesQuery->andWhere('cfv.value IN(:customFieldValue)');
					break;
				default:
					$customFieldValuesQuery->andWhere('cfv.value '.$operator.' :customFieldValue');
					break;
			}
			
			$customFieldValuesQuery
				->andWhere('cf.slug = :customFieldSlug')
				->setParameter('customFieldSlug', $slug)
				->setParameter('customFieldValue', $value)
			;
		}
		
		$customFieldValues = $customFieldValuesQuery
			->getQuery()
			->getResult()
		;
		
		$elementsIds = array_map(static function(CustomFieldValue $value) {
			return $value->getObjectId();
		}, $customFieldValues);
		
		/** @var QueryBuilder $elementsQuery */
		$elementsQuery = $this->em->getRepository($entity)->createQueryBuilder('element');
		$elementsQuery
			->andWhere('element.id IN (:elementsIds)')
			->setParameter('elementsIds', $elementsIds)
		;
		
		if($criterias) {
			foreach ($criterias as $key => $criteria) {
				if (!isset($criteria['prop'])) return "Il manque l'entrée prop de tableau.";
				if (!isset($criteria['operator'])) return "Il manque l'entrée operator de tableau.";
				if (!isset($criteria['value'])) return "Il manque l'entrée value de tableau.";
				
				$prop = $criteria['prop'];
				$operator = $criteria['operator'];
				$value = $criteria['value'];
				
				$elementsQuery->andWhere('element.'.$prop.' '.$operator.' :val'.$prop.$key);
				$elementsQuery->setParameter('val'.$prop.$key, $value);
			}
		}
		
		if($orders) {
			foreach ($orders as $criteria => $order) {
				$elementsQuery->addOrderBy('element.'.$criteria, $order);
			}
		}
		
		if($limit) {
			$elementsQuery->setMaxResults($limit);
		}
		
		if($offset) {
			$elementsQuery->setFirstResult($offset);
		}
		
		return $query ? $elementsQuery->getQuery() : $elementsQuery->getQuery()->getResult();
		
	}
}