<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\PostTag;
use Akyos\CoreBundle\Form\PostTagType;
use Akyos\CoreBundle\Repository\PostTagRepository;
use Akyos\CoreBundle\Repository\SeoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/post/tag", name="post_tag_")
 */
class PostTagController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @param PostTagRepository $postTagRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function index(PostTagRepository $postTagRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postTagRepository->createQueryBuilder('a');
        if($request->query->get('search')) {
            $query
                ->andWhere('a.title LIKE :keyword OR a.content LIKE :keyword')
                ->setParameter('keyword', '%'.$request->query->get('search').'%')
            ;
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page',1),12);

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Étiquettes d\'article',
            'entity' => 'PostTag',
            'view' => 'tag',
            'route' => 'post_tag',
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
        $postTag = new PostTag();
        $form = $this->createForm(PostTagType::class, $postTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($postTag);
            $entityManager->flush();

            return $this->redirectToRoute('post_tag_index');
        }

        return $this->render('@AkyosCore/crud/new.html.twig', [
            'el' => $postTag,
            'title' => 'Étiquette d\'article',
            'entity' => 'PostTag',
            'route' => 'post_tag',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param PostTag $postTag
     *
     * @return Response
     */
    public function edit(Request $request, PostTag $postTag): Response
    {
        $form = $this->createForm(PostTagType::class, $postTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_tag_index');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $postTag,
            'title' => 'Étiquette d\'article',
            'entity' => 'PostTag',
            'route' => 'post_tag',
            'view' => 'archive',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param PostTag $postTag
     * @param SeoRepository $seoRepository
     * @return Response
     */
    public function delete(Request $request, PostTag $postTag, SeoRepository $seoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$postTag->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($postTag);
            $entityManager->flush();
        }
        return $this->redirectToRoute('post_tag_index');
    }
}
