<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\OptionCategory;
use Akyos\CoreBundle\Form\OptionCategoryType;
use Akyos\CoreBundle\Repository\OptionCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/site_option/category", name="option_category_")
 */
class OptionCategoryController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(OptionCategoryRepository $optionCategoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $els = $paginator->paginate(
            $optionCategoryRepository->createQueryBuilder('a')->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

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
