<?php

namespace Akyos\CoreBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/export", name="export_")
 * @IsGranted("exports")
 */
// TODO => Remove useless LeagueCSV dependency and replace with native PHP fputcsv function
class ExportController extends AbstractController
{
	/**
	 * @Route("/", name="index")
	 * @param EntityManagerInterface $em
	 * @return Response
	 */
	public function index(EntityManagerInterface $em): Response
    {
		$entities = [];
		$meta = $em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entities[] = $m->getName();
		}

		return $this->render('@AkyosCore/export/index.html.twig', [
			'title' => 'Exporter',
			'entities' => $entities
		]);
	}

	/**
	 * @Route("/entity/params", name="entity_params")
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function getEntityParameter(Request $request): JsonResponse
	{
		$phpDocExtractor = new PhpDocExtractor();

		$reflectionExtractor = new ReflectionExtractor();
		$listExtractors = [$reflectionExtractor];
		$typeExtractors = [$phpDocExtractor, $reflectionExtractor];

		$propertyInfo = new PropertyInfoExtractor($listExtractors, $typeExtractors);

		$returnedTab = [];
		$allreadyCheck = [];

		$properties = $propertyInfo->getProperties($request->get('entity'));
		$returnedTab = $this->pushProperties($request->get('entity'), $properties, $propertyInfo, $returnedTab, $allreadyCheck, "");
		return new JsonResponse($returnedTab);
	}

	/**
	 * @Route("/dl", name="entity_dl")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function download(Request $request, EntityManagerInterface $entityManager): Response
    {
		$els = $entityManager->getRepository($request->get('entity'))->findAll();
		$rows = $request->get('rows');

		$filename = 'export.csv';
		$csv = Writer::createFromString();

		$records = [
			$rows
		];
		foreach ($els as $el) {
			$record = [];
			foreach ($rows as $row) {
				if (count(explode('.', $row)) > 1) {
					$value = $el;
					foreach (explode('.', $row) as $method) {
						$value = $value->{'get' . ucfirst($method)}();
						if (!$value) {
							break;
						}
					}
					$record[] = $this->valueToString($value);
				} else {
					$value = $el->{'get' . ucfirst($row)}();
					$record[] = $this->valueToString($value);
				}
			}
			$records[] = $record;
		}
		$csv->insertAll($records);

		$response = new Response($csv->getContent());
		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Content-Disposition', 'attachment;filename=' . $filename);
		return $response;
	}

    /**
     * @param $value
     * @return string
     */
	public function valueToString($value): string
    {
		if (is_array($value)) {
			$value = implode('|', $value);
		}
		if (is_object($value)) {
			switch (get_class($value)) {
				case 'DateTime':
					$value = $value->format('d/m/Y H:i:s');
					break;
				case 'Date':
					$value = $value->format('d/m/Y');
					break;
				default:
					$value = $value->toString();
			}
		}
		return $value;
	}

    /**
     * @param $entity
     * @param $properties
     * @param $propertyInfo
     * @param $returnedTab
     * @param $allreadyCheck
     * @param $currentDepth
     * @return mixed
     */
	public function pushProperties($entity, $properties, $propertyInfo, $returnedTab, $allreadyCheck, $currentDepth)
	{
		$allreadyCheck[] = $entity;
		foreach ($properties as $key => $p) {
			$propertyName = $properties[$key];
			$propertyType = $propertyInfo->getTypes($entity, $p);
			if ($propertyType && !in_array($propertyType[0]->getClassName(), $allreadyCheck, true) && count(explode('\\', $propertyType[0]->getClassName())) > 1) {
				$returnedTab = $this->pushProperties($propertyType[0]->getClassName(), $propertyInfo->getProperties($propertyType[0]->getClassName()), $propertyInfo, $returnedTab, $allreadyCheck, ($currentDepth ?? '') . $propertyName . '.');
			} else if ($propertyType) {
                if (!$propertyType[0]->getCollectionValueType()) {
                    $returnedTab[] = ['name' => $currentDepth . $propertyName, 'class' => $propertyType[0]->getClassName()];
                }
            } else {
                $returnedTab[] = ['name' => $currentDepth . $propertyName, 'class' => $entity];
            }
		}
		return $returnedTab;
	}
}
