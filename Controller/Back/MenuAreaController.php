<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Form\MenuAreaType;
use Akyos\CoreBundle\Repository\MenuAreaRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/menu/area", name="menu_area_")
 */
class MenuAreaController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     * @param MenuAreaRepository $menuAreaRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function index(MenuAreaRepository $menuAreaRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $els = $paginator->paginate(
            $menuAreaRepository->createQueryBuilder('ma')->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Zones de menu',
            'entity' => 'MenuArea',
            'route' => 'menu_area',
            'fields' => array(
                'ID' => 'Id',
                'Nom' => 'Name',
                'Slug' => 'Slug',
                'Description' => 'Description',
                'Menu' => 'Menu'
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
        $menuArea = new MenuArea();
        $form = $this->createForm(MenuAreaType::class, $menuArea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($menuArea);
            $entityManager->flush();

            return $this->redirectToRoute('menu_area_index');
        }

        return $this->render('@AkyosCore/crud/new.html.twig', [
            'el' => $menuArea,
            'title' => 'Zone de menu',
            'entity' => 'MenuArea',
            'route' => 'menu_area',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param MenuArea $menuArea
     *
     * @return Response
     */
    public function edit(Request $request, MenuArea $menuArea): Response
    {
        $form = $this->createForm(MenuAreaType::class, $menuArea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('menu_area_index');
        }

        return $this->render('@AkyosCore/crud/edit.html.twig', [
            'el' => $menuArea,
            'title' => 'Zone de menu',
            'entity' => 'MenuArea',
            'route' => 'menu_area',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param MenuArea $menuArea
     *
     * @return Response
     */
    public function delete(Request $request, MenuArea $menuArea): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menuArea->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $menu = $menuArea->getMenu();
            if ($menu) {
                $menu->setMenuArea(null);
            }
            $entityManager->remove($menuArea);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu_area_index');
    }
}
