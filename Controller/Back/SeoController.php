<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\Seo;
use Akyos\CoreBundle\Form\SeoType;
use Akyos\CoreBundle\Repository\SeoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/seo", name="seo_")
 */
class SeoController extends AbstractController
{
    /**
     * @Route("/render", name="render", methods={"GET"})
     * @param $type
     * @param $typeId
     * @param $route
     * @param SeoRepository $seoRepository
     *
     * @return Response
     */
    public function index($type, $typeId, $route, SeoRepository $seoRepository): Response
    {
        $seo = $seoRepository->findOneBy(array('type' => $type, 'typeId' => $typeId));

        if (!$seo) {
            $seo = new Seo();
            $seo->setTypeId($typeId);
            $seo->setType($type);
        }
        $formSeo = $this->createForm(SeoType::class, $seo);

        return $this->render('@AkyosCore/seo/render.html.twig', [
            'formSeo' => $formSeo->createView(),
        ]);
    }

    /**
     * @Route("/submit/{type}/{typeId}", name="submit", methods={"POST"}, options={"expose"=true})
     * @param $type
     * @param $typeId
     * @param Request $request
     * @param SeoRepository $seoRepository
     *
     * @return JsonResponse
     */
    public function submit($type, $typeId, Request $request, SeoRepository $seoRepository)
    {
        $seo = $seoRepository->findOneBy(array('type' => $type, 'typeId' => $typeId));

        if (!$seo) {
            $seo = new Seo();
            $seo->setTypeId($typeId);
            $seo->setType($type);
        }

        $formSeo = $this->createForm(SeoType::class, $seo);
        $formSeo->handleRequest($request);

        if ($formSeo->isSubmitted() && $formSeo->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($seo);
            $entityManager->flush();

            return new JsonResponse('valid');
        }

        return new JsonResponse('non');
    }
}
