<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Form\Handler\CrudHandler;
use Akyos\CoreBundle\Form\Type\Post\PostType;
use Akyos\CoreBundle\Form\Type\Post\NewPostType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PostRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Akyos\CoreBundle\Services\CoreService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/post", name="post_")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     * @param PostRepository $postRepository
     * @param CoreOptionsRepository $coreOptionsRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param CrudHandler $crudHandler
     *
     * @return Response
     */
    public function index(PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository, PaginatorInterface $paginator, Request $request, CrudHandler $crudHandler): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        $els = $paginator->paginate(
            $postRepository->createQueryBuilder('a')->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        $post = new Post();
        $post->setPublished(false);
        $post->setPosition($postRepository->count([]));
        $newPostForm = $this->createForm(NewPostType::class, $post);

        if ($crudHandler->new($newPostForm, $request)) {
            return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
        }

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Article',
            'entity' => 'Post',
            'view' => 'single',
            'route' => 'post',
            'formModal' => $newPostForm->createView(),
            'bundle' => 'CoreBundle',
            'fields' => [
                'Title' => 'Title',
                'ID' => 'Id',
                'Position' => 'Position',
                'Status' => 'Published',
            ],
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param PostRepository $postRepository
     * @param CoreOptionsRepository $coreOptionsRepository
     *
     * @return Response
     */
    public function new(PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $post = new Post();
        $post->setPublished(false);
        $post->setTitle("Nouvel article");
        $post->setPosition($postRepository->count(array()));
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('post_edit', ['id' => $post->getId()]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param Post $post
     * @param CoreOptionsRepository $coreOptionsRepository
     * @param CoreService $coreService
     *
     * @return Response
     */
    public function edit(Request $request, Post $post, CoreOptionsRepository $coreOptionsRepository, CoreService $coreService): Response
    {
        $entity = 'Post';
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
            if (!$form->isSubmitted()) {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::initCloneComponents', ['type' => $entity, 'typeId' => $post->getId()]);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::tempToProd', ['type' => 'Post', 'typeId' => $post->getId()]);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($request->getUri());
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $post,
            'title' => 'Article',
            'entity' => 'Post',
            'route' => 'post',
            'view' => 'single',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Post $post
     * @param PostRepository $postRepository
     * @param CoreOptionsRepository $coreOptionsRepository
     * @param SeoRepository $seoRepository
     *
     * @param CoreService $coreService
     *
     * @return Response
     */
    public function delete(Request $request, Post $post, PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository, SeoRepository $seoRepository, CoreService $coreService): Response
    {
        $entity = 'Post';
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            if ($coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $entity)) {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::onDeleteEntity', ['type' => $entity, 'typeId' => $post->getId()]);
            }

            $seo = $seoRepository->findOneBy(array('type' => $entity, 'typeId' => $post->getId()));
            if ($seo) {
                $entityManager->remove($seo);
            }

            $entityManager->remove($post);
            $entityManager->flush();

            $position = 0;
            foreach ($postRepository->findBy([], ['position' => 'ASC']) as $el) {
                $el->setPosition($position);
                $position++;
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
}
