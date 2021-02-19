<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\Option;
use Akyos\CoreBundle\Form\NewOptionType;
use Akyos\CoreBundle\Form\OptionType;
use Akyos\CoreBundle\Repository\OptionCategoryRepository;
use Akyos\CoreBundle\Repository\OptionRepository;
use Akyos\CoreBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/site_option", name="option_")
 * @isGranted("options-du-site")
 */
class OptionController extends AbstractController
{
	/**
	 * @Route("/", name="index", methods={"GET", "POST"})
	 * @param Request $request
	 * @param OptionRepository $optionRepository
	 * @param PageRepository $pageRepository
	 * @param OptionCategoryRepository $categoryRepository
	 *
	 * @return Response
	 */
	public function index(Request $request, OptionRepository $optionRepository, PageRepository $pageRepository, OptionCategoryRepository $categoryRepository): Response
	{
		$em = $this->getDoctrine()->getManager();

		$option = new Option();
		$newOptionForm = $this->createForm(NewOptionType::class, $option);

		if ($request->getMethod('POST')) {
			$newOptionForm->handleRequest($request);
			if ($newOptionForm->isSubmitted() && $newOptionForm->isValid()) {
				try {
					$em->persist($option);
					$em->flush();
					$this->addFlash('success', "Création du réglage effectuée avec succès !");
				} catch (Exception $e) {
					$this->addFlash('danger', "Une erreur s'est produite lors de la création du réglage, merci de réssayer.");
				}
			}
		}

		$params = array();

		$pageArray = array();
		foreach ($pageRepository->findAll() as $page) {
			$pageArray[$page->getTitle()] = $request->getUriForPath('/' . $page->getSlug());
		}

		foreach ($optionRepository->findAll() as $option) {
			$optionForm = $this->createForm(OptionType::class, $option, ['option' => $option->getId(), 'pages' => $pageArray]);
			if ($request->getMethod('POST')) {
				$optionForm->handleRequest($request);
				if ($optionForm->isSubmitted() && $optionForm->isValid()) {
					try {
						$em->persist($option);
						$em->flush();
						$this->addFlash('success', "Modification du réglage effectuée avec succès !");
					} catch (Exception $e) {
						$this->addFlash('danger', "Une erreur s'est produite lors de la modification du réglage, merci de réssayer.");
					}
				}
			}
			$params[$option->getSlug()] = $optionForm->createView();
		}

		return $this->render('@AkyosCore/option/index.html.twig', [
			'options' => $categoryRepository->findAll(),
			'params' => $params,
			'new_option_form' => $newOptionForm->createView(),
			'title' => 'Réglages',
			'entity' => 'Option',
			'route' => 'option',
			'fields' => array(
				'Title' => 'Title',
				'ID' => 'Id',
				'Valeur' => 'Value'
			),
		]);
	}

	/**
	 * @Route("/remove/{id}", name="delete")
	 * @param Option $option
	 *
	 * @return Response
	 */
	public function delete(Option $option): Response
	{
		$em = $this->getDoctrine()->getManager();

		$em->remove($option);
		$em->flush();

		return $this->redirectToRoute('option_index');
	}
}
