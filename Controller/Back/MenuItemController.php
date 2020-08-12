<?php


namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Form\MenuItemType;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Akyos\CoreBundle\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/menu/item", name="menu_item_")
 */
class MenuItemController extends AbstractController
{
    /**
     * @Route("/{id}/edit/{menu}", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param MenuItem $menuItem
     * @param $menu
     * @param MenuRepository $menuRepository
     *
     * @return Response
     */
    public function edit(Request $request, MenuItem $menuItem, $menu, MenuRepository $menuRepository): Response
    {
        $menu = $menuRepository->find($menu);
        $form = $this->createForm(MenuItemType::class, $menuItem, array('menu' => $menu));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return new Response('valid');
        }

        return $this->render('@AkyosCore/menu_item/edit.html.twig', [
            'menu_item' => $menuItem,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param MenuItem $menuItem
     *
     * @return Response
     */
    public function delete(Request $request, MenuItem $menuItem): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menuItem->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($menuItem);
            $entityManager->flush();
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
