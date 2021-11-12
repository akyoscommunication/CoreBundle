<?php

namespace Akyos\CoreBundle\Services;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Entity\CustomFieldValue;
use Akyos\CoreBundle\Repository\CustomFieldRepository;
use Akyos\CoreBundle\Repository\CustomFieldValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use ReflectionClassConstant;

class CoreService
{
	private EntityManagerInterface $em;
	private CustomFieldValueRepository $customFieldValueRepository;
	private CustomFieldRepository $customFieldRepository;
	
	public function __construct(EntityManagerInterface $em, CustomFieldValueRepository $customFieldValueRepository, CustomFieldRepository $customFieldRepository)
	{
		$this->em = $em;
		$this->customFieldValueRepository = $customFieldValueRepository;
		$this->customFieldRepository = $customFieldRepository;
	}

    /**
     * @param $entity
     * @return bool
     */
	public function checkIfSingleEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasSingleEntities(), true)) {
				return true;
			}
            return false;
        }
        return false;
    }

    /**
     * @param $entity
     * @return bool
     */
	public function checkIfArchiveEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasArchiveEntities(), true)) {
				return true;
			}
            return false;
        }
        return false;
    }

    /**
     * @param $entity
     * @return bool
     */
	public function checkIfSeoEnable($entity): bool
	{
		$opt = $this->em->getRepository(CoreOptions::class)->findAll();
		if ($opt) {
			if (in_array($entity, $opt[0]->getHasSeoEntities(), true)) {
				return true;
			}
            return false;
        }
        return false;
    }

    /**
     * @param $bundle
     * @param $options
     * @param $entity
     * @return bool
     */
	public function checkIfBundleEnable($bundle, $options, $entity): bool
    {
		if (class_exists($bundle)) {
			$opt = $this->em->getRepository($options)->findAll();
			if ($opt) {
				if (in_array($entity, $opt[0]->getHasBuilderEntities(), true)) {
					return true;
				}
				return false;
			}
			return false;
		}
		return false;
	}

    /**
     * @param string $entitySlug
     * @return array
     */
	public function getEntityAndFullString(string $entitySlug): array
    {
		$entityFullName = null;
		$entity = null;
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
            $constant_reflex = new ReflectionClassConstant($m->getName(), 'ENTITY_SLUG');
            $constant_value = $constant_reflex->getValue();
            if((null !== $constant_value) && $m->getName()::ENTITY_SLUG === $entitySlug) {
                $entityFullName = $m->getName();
                $entity = array_reverse(explode('\\', $entityFullName))[0];
            }
		}
		
		return [
			$entityFullName,
			$entity
		];
	}

    /**
     * @param string $customFieldSlug
     * @param $object
     * @return object|string|null
     */
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
		
		if($customField->getType() === 'entity') {
            if ($customFieldValue->getValue()) {
                $customFieldValue = $this->em->getRepository($customField->getEntity())->find($customFieldValue->getValue());
            } else {
                $customFieldValue = null;
            }
        } else {
            $customFieldValue = $customFieldValue->getValue();
		}
		
		return $customFieldValue;
	}

    /**
     * @param string $customFieldSlug
     * @param $object
     * @param $value
     * @return bool|null
     */
	public function setCustomField(string $customFieldSlug, $object, $value): ?bool
    {
		$customField = $this->customFieldRepository->findOneBy(['slug' => $customFieldSlug]);
		if(!$customField) {
			return null;
		}
		
		$customFieldValue = $this->customFieldValueRepository->findOneBy(['customField' => $customField, 'objectId' => $object->getId()]);
		if(!$customFieldValue) {
			return null;
		}
		
		if($customField->getType() === 'entity') {
            $customFieldValue->setValue($value->getId());
        } else {
            $customFieldValue->setValue($value);
		}
		return true;
	}
	
	/**
	 * @param array $customFieldCriterias
	 * @param string $entity
	 * @param array|null $criterias
	 * @param array|null $orders
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param bool $query
	 * @return Query|int|mixed|string
	 */
	public function searchByCustomField(array $customFieldCriterias, string $entity, ?array $criterias = null, ?array $orders = null, ?int $limit = null, ?int $offset = null, $query = false)
	{
		$customFieldValuesQuery = $this->customFieldValueRepository->createQueryBuilder('cfv')
			->innerJoin('cfv.customField', 'cf')
		;
		
		foreach ($customFieldCriterias as $customField) {
			if (!isset($customField['slug'])) {
                return "Il manque l'entrée slug dans le tableau.";
            }
			if (!isset($customField['operator'])) {
                return "Il manque l'entrée operator dans le tableau.";
            }
			if (!isset($customField['value'])) {
                return "Il manque l'entrée value dans le tableau.";
            }
			
			$slug = $customField['slug'];
			$operator = $customField['operator'];
			$value = $customField['value'];
			
			if($operator === 'IN') {
                $customFieldValuesQuery->andWhere('cfv.value IN(:customFieldValue)');
            } else {
                $customFieldValuesQuery->andWhere('cfv.value '.$operator.' :customFieldValue');
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

        $elementsQuery = $this->em->getRepository($entity)->createQueryBuilder('element');
		$elementsQuery
			->andWhere('element.id IN (:elementsIds)')
			->setParameter('elementsIds', $elementsIds)
		;
		
		if($criterias) {
			foreach ($criterias as $key => $criteria) {
				if (!isset($criteria['prop'])) {
                    return "Il manque l'entrée prop dans le tableau.";
                }
				if (!isset($criteria['operator'])) {
                    return "Il manque l'entrée operator dans le tableau.";
                }
				if (!isset($criteria['value'])) {
                    return "Il manque l'entrée value dans le tableau.";
                }
				
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