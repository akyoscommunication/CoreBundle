<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\RgpdOptions;
use Akyos\CoreBundle\Form\RgpdOptionsType;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\RgpdOptionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/rgpd/options", name="rgpd_options")
 */
class RgpdOptionsController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     * @param RgpdOptionsRepository $rgpdOptionsRepository
     * @param PageRepository $pageRepository
     * @param Request $request
     * @return Response
     */
    public function index(RgpdOptionsRepository $rgpdOptionsRepository, PageRepository $pageRepository, Request $request): Response
    {
        $rgpdOption = $rgpdOptionsRepository->findAll();
        if(!$rgpdOption) {
            $rgpdOption = new RgpdOptions();
        } else {
            $rgpdOption = $rgpdOption[0];
        }

        $form = $this->createForm(RgpdOptionsType::class, $rgpdOption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rgpdOption);
            $entityManager->flush();

            return $this->redirectToRoute('rgpd_options');
        }

        return $this->render('@AkyosCore/rgpd_options/new.html.twig', [
            'rgpd_option' => $rgpdOption,
            'form' => $form->createView(),
        ]);
    }
}
