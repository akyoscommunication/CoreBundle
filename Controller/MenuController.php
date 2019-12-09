<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\Menu;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Form\MenuItemType;
use Akyos\CoreBundle\Form\MenuType;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Akyos\CoreBundle\Repository\MenuRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/menu")
 */
class MenuController extends AbstractController
{
    /**
     * @Route("/", name="menu_index", methods={"GET"})
     */
    public function index(MenuRepository $menuRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $els = $paginator->paginate(
            $menuRepository->createQueryBuilder('a')->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('@AkyosCore/crud/index.html.twig', [
            'els' => $els,
            'title' => 'Menu',
            'route' => 'menu',
            'fields' => array(
                'ID' => 'Id',
                'Title' => 'Title',
                'Slug' => 'Slug',
                'Zone de menu' => 'MenuArea'
            ),
        ]);
    }

    /**
     * @Route("/new", name="menu_new", methods={"GET","POST"})
     */
    public function new(Request $request, MenuItemRepository $menuItemRepository): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($menu);
            $entityManager->flush();

            return $this->redirectToRoute('menu_edit', ['id' => $menu->getId()]);
        }

        return $this->render('@AkyosCore/menu/new.html.twig', [
            'el' => $menu,
            'title' => 'Menu',
            'route' => 'menu',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="menu_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Menu $menu, MenuItemRepository $menuItemRepository): Response
    {
        $menuItem = new MenuItem();
        $menuItem->setMenu($menu);
        $form = $this->createForm(MenuType::class, $menu);
        $formItem = $this->createForm(MenuItemType::class, $menuItem, array('menu' => $menu));
        $form->handleRequest($request);
        $formItem->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('menu_edit', ['id' => $menu->getId()]);
        }

        if ($formItem->isSubmitted() && $formItem->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            if ($formItem->get('menuItemParent')) {
                $menuItem->setPosition($menuItemRepository->count(array('menu' => $menu, 'menuItemParent' => $formItem->getData()->getMenuItemParent())));
            } else {
                $menuItem->setPosition($menuItemRepository->count(array('menu' => $menu, 'menuItemParent' => null)));
            }
            $entityManager->persist($menuItem);
            $entityManager->flush();

            return $this->redirectToRoute('menu_edit', ['id' => $menu->getId()]);
        }

        return $this->render('@AkyosCore/menu/edit.html.twig', [
            'el' => $menu,
            'title' => 'Menu',
            'route' => 'menu',
            'menuItems' => $menuItemRepository->findBy(array('menu' => $menu->getId()), array('position' => 'ASC')),
            'formItem' => $formItem->createView(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="menu_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Menu $menu): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menu->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $menuArea = $menu->getMenuArea();
            if ($menuArea) {
                $menuArea->setMenu(null);
            }
            $entityManager->remove($menu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu_index');
    }

    /**
     * @Route("/{id}/item/change-position", name="menu_change_position_menu_item", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function changePositionMenuItem(Request $request, int $id, MenuItemRepository $menuItemRepository, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuItemRepository->findBy(array('id' => $id));

        if ($request->request->get('data')) {
            foreach ($request->request->get('data') as $key => $item) {
                $menuParentItem = $menuItemRepository->findOneBy(array('id' => $item['parent']));
                $menuParentItem->setPosition($key);
                $menuParentItem->setMenuItemParent(NULL);
                $this->getDoctrine()->getManager()->persist($menuParentItem);
                if (isset($item['childs'])) {
                    foreach ($item['childs'] as $subKey => $subItem) {
                        $menuChildItem = $menuItemRepository->findOneBy(array('id' => $subItem));
                        $menuChildItem->setPosition($subKey);
                        $menuChildItem->setMenuItemParent($menuParentItem);
                        $this->getDoctrine()->getManager()->persist($menuChildItem);
                    }
                }
            }
        }
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse('valid');
    }
}
