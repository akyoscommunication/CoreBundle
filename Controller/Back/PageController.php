<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\Page;
use Akyos\CoreBundle\Form\Handler\CrudHandler;
use Akyos\CoreBundle\Form\Type\Page\NewPageType;
use Akyos\CoreBundle\Form\Type\Page\PageType;
use Akyos\CoreBundle\Repository\PageRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Akyos\CoreBundle\Services\CoreService;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/page", name="page_")
 */
class PageController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     * @param PageRepository $pageRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param CrudHandler $crudHandler
     *
     * @return Response
     */
    public function index(PageRepository $pageRepository, PaginatorInterface $paginator, Request $request, CrudHandler $crudHandler): Response
    {
        $query = $pageRepository->createQueryBuilder('a');
        if($request->query->get('search')) {
            $query
                ->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword')
                ->setParameter('keyword', '%'.$request->query->get('search').'%')
            ;
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page',1),12);

        $page = new Page();
        $page->setPublished(false);
        $page->setPosition($pageRepository->count([]));
        $newPageForm = $this->createForm(NewPageType::class, $page);

        if ($crudHandler->new($newPageForm, $request)) {
            return $this->redirectToRoute('page_edit', ['id' => $page->getId()]);
        }

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Page',
            'entity' => 'Page',
            'view' => 'page',
            'route' => 'page',
            'header_route' => 'page',
            'formModal' => $newPageForm->createView(),
            'bundle' => 'CoreBundle',
            'fields' => array(
                'ID' => 'Id',
                'Titre' => 'Title',
                'Slug' => 'Slug',
                'Position' => 'Position',
                'Actif ?' => 'Published',
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
        $page->setPosition($pageRepository->count([]));
        $entityManager->persist($page);
        $entityManager->flush();

        return $this->redirectToRoute('page_edit', ['id' => $page->getId()]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param Page $page
     * @param CoreService $coreService
     *
     * @return Response
     */
    public function edit(Request $request, Page $page, CoreService $coreService, ContainerInterface $container): Response
    {
        $entity = 'Page';
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        $classBuilder = 'Akyos\BuilderBundle\AkyosBuilderBundle' ;
        $classBuilderOption = 'Akyos\BuilderBundle\Entity\BuilderOptions' ;
        if ($coreService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity)) {
            if (!$form->isSubmitted()) {
                $container->get('render.builder')->initCloneComponents($entity, $page->getId());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            if ($coreService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity)) {
                $container->get('render.builder')->tempToProd($entity, $page->getId());
            }
            $em->flush();

            return $this->redirect($request->getUri());
        } elseif($form->isSubmitted() && !($form->isValid())) {
            throw $this->createNotFoundException("Formulaire invalide.");
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $page,
            'title' => '"'.$page->getTitle().'"',
            'entity' => 'Page',
            'route' => 'page',
            'header_route' => 'page_index',
            'parameters' => [
                'slug' => $page->getSlug(),
            ],
            'view' => 'page',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Page $page
     * @param PageRepository $pageRepository
     * @param SeoRepository $seoRepository
     * @param CoreService $coreService
     * @param ContainerInterface $container
     *
     * @return Response
     */
    public function delete(Request $request, Page $page, PageRepository $pageRepository, SeoRepository $seoRepository, CoreService $coreService, ContainerInterface $container): Response
    {
        $entity = 'Page';
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $classBuilder = 'Akyos\BuilderBundle\AkyosBuilderBundle' ;
            $classBuilderOption = 'Akyos\BuilderBundle\Entity\BuilderOptions' ;
            if ($coreService->checkIfBundleEnable($classBuilder, $classBuilderOption, $entity)) {
                $container->get('render.builder')->onDeleteEntity($entity, $page->getId());
            }

            $seo = $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $page->getId()));
            if ($seo) {
                $entityManager->remove($seo);
            }
            $entityManager->remove($page);
            $entityManager->flush();

            $position = 0;
            foreach ($pageRepository->findBy([], ['position' => 'ASC']) as $el) {
                $el->setPosition($position);
                $position++;
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('page_index');
    }
}
