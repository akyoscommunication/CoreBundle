<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\Menu;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Form\MenuItemType;
use Akyos\CoreBundle\Form\MenuType;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Akyos\CoreBundle\Repository\MenuRepository;
use JsonException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/admin/menu")
 * @isGranted("liste-de-menus")
 */
// TODO => Move non routes functions in a MenuService class
class MenuController extends AbstractController
{
	private MenuItemRepository $menuItemRepository;
	private MenuRepository $menuRepository;

	public function __construct(MenuItemRepository $menuItemRepository, MenuRepository $menuRepository)
	{
		$this->menuItemRepository = $menuItemRepository;
		$this->menuRepository = $menuRepository;
	}

	/**
	 * @Route("/", name="menu_index", methods={"GET"})
	 * @param PaginatorInterface $paginator
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function index(PaginatorInterface $paginator, Request $request): Response
	{
		$query = $this->menuRepository->createQueryBuilder('a');
		if ($request->query->get('search')) {
			$query
				->leftJoin('a.menuArea', 'menuArea')
				->andWhere('a.title LIKE :keyword OR a.slug LIKE :keyword OR menuArea.name LIKE :keyword')
				->setParameter('keyword', '%' . $request->query->get('search') . '%');
		}
		$els = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 12);

		return $this->render('@AkyosCore/crud/index.html.twig', [
			'els' => $els,
			'title' => 'Menu',
			'route' => 'menu',
			'fields' => [
				'ID' => 'Id',
				'Titre' => 'Title',
				'Slug' => 'Slug',
				'Zone de menu' => 'MenuArea'
			],
		]);
	}

	/**
	 * @Route("/new", name="menu_new", methods={"GET","POST"})
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function new(Request $request): Response
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
	 * @param Request $request
	 * @param Menu $menu
	 * @param MenuItemRepository $menuItemRepository
	 *
	 * @return Response
	 */
	public function edit(Request $request, Menu $menu, MenuItemRepository $menuItemRepository): Response
	{
		$menuItem = new MenuItem();
		$menuItem->setMenu($menu);
		$form = $this->createForm(MenuType::class, $menu);
		$formItem = $this->createForm(MenuItemType::class, $menuItem, ['menu' => $menu]);
		$form->handleRequest($request);
		$formItem->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('menu_edit', ['id' => $menu->getId()]);
		}

		if ($formItem->isSubmitted() && $formItem->isValid()) {
			$entityManager = $this->getDoctrine()->getManager();
			if ($formItem->get('menuItemParent')) {
				$menuItem->setPosition($menuItemRepository->count(['menu' => $menu, 'menuItemParent' => $formItem->getData()->getMenuItemParent()]));
			} else {
				$menuItem->setPosition($menuItemRepository->count(['menu' => $menu, 'menuItemParent' => null]));
			}
			$entityManager->persist($menuItem);
			$entityManager->flush();

			return $this->redirectToRoute('menu_edit', ['id' => $menu->getId()]);
		}

		return $this->render('@AkyosCore/menu/edit.html.twig', [
			'el' => $menu,
			'title' => 'Menu',
			'route' => 'menu',
			'menuItems' => $menuItemRepository->findBy(['menu' => $menu->getId()], ['position' => 'ASC']),
			'formItem' => $formItem->createView(),
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{id}", name="menu_delete", methods={"DELETE"})
	 * @param Request $request
	 * @param Menu $menu
	 *
	 * @return Response
	 */
	public function delete(Request $request, Menu $menu): Response
	{
		if ($this->isCsrfTokenValid('delete' . $menu->getId(), $request->request->get('_token'))) {
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
     * @return JsonResponse
     * @throws JsonException
     */
	public function changePositionMenuItem(Request $request): JsonResponse
	{
		$newPositions = json_decode((string)$request->getContent(), true, 512, JSON_THROW_ON_ERROR);

		if ($newPositions) {
			foreach ($newPositions['resultMenuItem'] as $key => $item) {
			    /** @var MenuItem $menuParentItem */
				$menuParentItem = $this->menuItemRepository->findOneBy(['id' => $item['parent']]);
				$menuParentItem->setPosition($key);
				$menuParentItem->setMenuItemParent(NULL);
				$this->getDoctrine()->getManager()->persist($menuParentItem);
				if (!empty($item['childs'])) {
					foreach ($item['childs'] as $subKey => $subItem) {
						$this->subItemChangePosition($subKey, $subItem, $menuParentItem);
					}
				}
			}
		}
		$this->getDoctrine()->getManager()->flush();

		return new JsonResponse('valid');
	}

    /**
     * @param $key
     * @param $item
     * @param $parent
     * @return bool
     */
	public function subItemChangePosition($key, $item, $parent): bool
    {
        /** @var MenuItem $menuChildItem */
		$menuChildItem = $this->menuItemRepository->findOneBy(['id' => $item]);
		$menuChildItem->setPosition($key);
		$menuChildItem->setMenuItemParent($parent);
		$this->getDoctrine()->getManager()->persist($menuChildItem);
		if (!empty($item['childs'])) {
			foreach ($item['childs'] as $subKey => $subItem) {
				$this->subItemChangePosition($subKey, $subItem, $menuChildItem);
			}
		}
		return true;
	}
}
