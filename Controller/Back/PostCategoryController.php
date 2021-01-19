<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\PostCategory;
use Akyos\CoreBundle\Form\PostCategoryType;
use Akyos\CoreBundle\Repository\PostCategoryRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/post/category", name="post_category_")
 * @isGranted("categories-darticles")
 */
class PostCategoryController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @param PostCategoryRepository $postCategoryRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function index(PostCategoryRepository $postCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postCategoryRepository->createQueryBuilder('a');
        if($request->query->get('search')) {
            $query
                ->andWhere('a.title LIKE :keyword OR a.content LIKE :keyword')
                ->setParameter('keyword', '%'.$request->query->get('search').'%')
            ;
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page',1),12);

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Catégorie d\'article',
            'entity' => 'PostCategory',
            'view' => 'taxonomy',
            'route' => 'post_category',
            'fields' => array(
                'ID' => 'Id',
                'Title' => 'Title',
                'Description' => 'Content',
            ),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $postCategory = new PostCategory();
        $form = $this->createForm(PostCategoryType::class, $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($postCategory);
            $entityManager->flush();

            return $this->redirectToRoute('post_category_index');
        }

        return $this->render('@AkyosCore/crud/new.html.twig', [
            'el' => $postCategory,
            'title' => 'Catégorie d\'article',
            'entity' => 'PostCategory',
            'route' => 'post_category',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param PostCategory $postCategory
     *
     * @return Response
     */
    public function edit(Request $request, PostCategory $postCategory): Response
    {
        $form = $this->createForm(PostCategoryType::class, $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_category_index');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $postCategory,
            'title' => 'Catégorie d\'article',
            'entity' => 'PostCategory',
            'route' => 'post_category',
            'view' => 'archive',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param PostCategory $postCategory
     * @param SeoRepository $seoRepository
     * @return Response
     */
    public function delete(Request $request, PostCategory $postCategory, SeoRepository $seoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$postCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($postCategory);
            $entityManager->flush();
        }
        return $this->redirectToRoute('post_category_index');
    }
}
