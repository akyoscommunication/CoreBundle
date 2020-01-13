<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Form\PageType;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/page", name="page_")
 */
class PageController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @param PageRepository $pageRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PageRepository $pageRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $els = $paginator->paginate(
            $pageRepository->createQueryBuilder('a')->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Page',
            'entity' => 'Page',
            'view' => 'page',
            'route' => 'page',
            'bundle' => 'CoreBundle',
            'fields' => array(
                'ID' => 'Id',
                'Titre' => 'Title',
                'Slug' => 'Slug',
                'Position' => 'Position',
                'Status' => 'Published',
            ),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param PageRepository $pageRepository
     * @return Response
     */
    public function new(PageRepository $pageRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $page = new Page();
        $page->setPublished(false);
        $page->setTitle("Nouvelle page");
        $page->setPosition($pageRepository->count(array()));
        $entityManager->persist($page);
        $entityManager->flush();

        return $this->redirectToRoute('page_edit', ['id' => $page->getId()]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param Page $page
     * @return Response
     */
    public function edit(Request $request, Page $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if (($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true")) {
            if (!$form->isSubmitted()) {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::initCloneComponents', ['type' => 'Page', 'typeId' => $page->getId()]);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true") {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::tempToProd', ['type' => 'Page', 'typeId' => $page->getId()]);
            }

            $em->flush();

            return new JsonResponse('valid');
        } elseif($form->isSubmitted() && !($form->isValid())) {
            return new JsonResponse('not valid');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $page,
            'title' => 'Page',
            'entity' => 'Page',
            'route' => 'page',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Page $page
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @return Response
     */
    public function delete(Request $request, Page $page, PageRepository $pageRepository, SeoRepository $seoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            if ($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Page'])->getContent() === "true") {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::onDeleteEntity', ['type' => 'Page', 'typeId' => $page->getId()]);
            }
            $seo = $seoRepository->findOneBy(array('type' => 'Page', 'typeId' => $page->getId()));
            if ($seo) {
                $entityManager->remove($seo);
            }
            $entityManager->remove($page);
            $entityManager->flush();

            $position = 0;
            foreach ($pageRepository->findBy(array(), array('position' => 'ASC')) as $el) {
                $el->setPosition($position);
                $position++;
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('page_index');
    }
}
