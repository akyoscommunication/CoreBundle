<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\CoreOptions;
use Akyos\CoreBundle\Form\CoreOptionsType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/core/options", name="core_options")
 */
class CoreOptionsController extends AbstractController
{
    /**
     * @Route("/", name="", methods={"GET", "POST"})
     */
    public function index(CoreOptionsRepository $coreOptionsRepository, Request $request): Response
    {
        $coreOption = $coreOptionsRepository->findAll();
        if(!$coreOption) {
            $coreOption = new CoreOptions();
        } else {
            $coreOption = $coreOption[0];
        }

        $entities = array();
        $em =$this->getDoctrine()->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if(!preg_match('/Component|Option|Menu|ContactForm|Seo|User|PostCategory/i', $m->getName())) {
                $entities[] = $m->getName();
            }
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
