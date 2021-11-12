<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Form\CoreOptionsType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/core/options", name="core_options")
 * @isGranted("options-du-core")
 */
class CoreOptionsController extends AbstractController
{
	/**
	 * @Route("/", name="", methods={"GET", "POST"})
	 * @param CoreOptionsRepository $coreOptionsRepository
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function index(CoreOptionsRepository $coreOptionsRepository, Request $request): Response
	{
		$coreOption = $coreOptionsRepository->findAll();
		if (!$coreOption) {
			$coreOption = new CoreOptions();
		} else {
			$coreOption = $coreOption[0];
		}

		$entities = [];
		$em = $this->getDoctrine()->getManager();
		$meta = $em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entities[] = $m->getName();
		}

		$form = $this->createForm(CoreOptionsType::class, $coreOption, [
			'entities' => $entities
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($coreOption);
			$entityManager->flush();

			return $this->redirectToRoute('core_options');
		}

		return $this->render('@AkyosCore/core_options/new.html.twig', [
			'core_option' => $coreOption,
			'form' => $form->createView(),
		]);
	}
}
