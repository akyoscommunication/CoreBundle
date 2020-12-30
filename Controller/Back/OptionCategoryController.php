<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\OptionCategory;
use Akyos\CoreBundle\Form\OptionCategoryType;
use Akyos\CoreBundle\Repository\OptionCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/site_option/category", name="option_category_")
 * @isGranted("categorie-doptions-du-site")
 */
class OptionCategoryController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @param OptionCategoryRepository $optionCategoryRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function index(OptionCategoryRepository $optionCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $optionCategoryRepository->createQueryBuilder('a');
        if($request->query->get('search')) {
            $query
                ->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword')
                ->setParameter('keyword', '%'.$request->query->get('search').'%')
            ;
        }
        $els = $paginator->paginate($query->getQuery(), $request->query->getInt('page',1),12);

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Catégorie d\'options',
            'entity' => 'Option',
            'route' => 'option_category',
            'fields' => array(
                'ID' => 'Id',
                'Slug' => 'Slug',
                'Title' => 'Title',
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
        $optionCategory = new OptionCategory();
        $form = $this->createForm(OptionCategoryType::class, $optionCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($optionCategory);
            $entityManager->flush();

            return $this->redirectToRoute('option_category_index');
        }

        return $this->render('@AkyosCore/crud/new.html.twig', [
            'el' => $optionCategory,
            'title' => 'Catégorie d\'options',
            'entity' => 'Option',
            'route' => 'option_category',
            'fields' => array(
                'ID' => 'Id',
                'Slug' => 'Slug',
                'Title' => 'Title',
            ),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param OptionCategory $optionCategory
     *
     * @return Response
     */
    public function edit(Request $request, OptionCategory $optionCategory): Response
    {
        $form = $this->createForm(OptionCategoryType::class, $optionCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('option_category_index');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $optionCategory,
            'title' => 'Catégorie d\'options',
            'entity' => 'Option',
            'route' => 'option_category',
            'fields' => array(
                'Title' => 'Title',
                'ID' => 'Id'
            ),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param OptionCategory $optionCategory
     *
     * @return Response
     */
    public function delete(Request $request, OptionCategory $optionCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$optionCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($optionCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('option_category_index');
    }
}
