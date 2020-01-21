<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\Post;
use Akyos\CoreBundle\Form\PostType;
use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\PostRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/post", name="post_")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository, PaginatorInterface $paginator, Request $request): Response
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

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Article',
            'entity' => 'Post',
            'view' => 'single',
            'route' => 'post',
            'bundle' => 'CoreBundle',
            'fields' => array(
                'Title' => 'Title',
                'ID' => 'Id',
                'Position' => 'Position',
                'Status' => 'Published',
            ),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request, PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository): Response
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
     */
    public function edit(Request $request, Post $post, CoreOptionsRepository $coreOptionsRepository): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Post'])->getContent() === "true") {
            if (!$form->isSubmitted()) {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::initCloneComponents', ['type' => 'Post', 'typeId' => $post->getId()]);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Post'])->getContent() === "true") {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::tempToProd', ['type' => 'Post', 'typeId' => $post->getId()]);
            }

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse('valid');
        } elseif($form->isSubmitted() && !($form->isValid())) {
            // TODO => error mess
            return new JsonResponse('not valid');
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
     */
    public function delete(Request $request, Post $post, PostRepository $postRepository, CoreOptionsRepository $coreOptionsRepository, SeoRepository $seoRepository): Response
    {
        $coreOptions = $coreOptionsRepository->findAll();
        if($coreOptions) {
            if(!$coreOptions[0]->getHasPosts()) {
                return $this->redirectToRoute('core_index');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            if ($this->forward('Akyos\CoreBundle\Controller\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => 'Post'])->getContent() === "true") {
                $this->forward('Akyos\BuilderBundle\Controller\BuilderController::onDeleteEntity', ['type' => 'Post', 'typeId' => $post->getId()]);
            }

            $seo = $seoRepository->findOneBy(array('type' => 'Post', 'typeId' => $post->getId()));
            if ($seo) {
                $entityManager->remove($seo);
            }

            $entityManager->remove($post);
            $entityManager->flush();

            $position = 0;
            foreach ($postRepository->findBy(array(), array('position' => 'ASC')) as $el) {
                $el->setPosition($position);
                $position++;
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
}
