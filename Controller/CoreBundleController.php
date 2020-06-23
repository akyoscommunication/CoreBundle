<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="core_")
 */
class CoreBundleController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('@AkyosCore/core_bundle/index.html.twig', [
            'title' => 'Tableau de Bord',
        ]);
    }

    /**
     * @Route("/change-position/{route}/{el}/{id}/{bundle}", name="change_position", methods={"POST"})
     * @param $route
     * @param $el
     * @param $id
     * @param Request $request
     * @param $bundle
     * @return RedirectResponse
     */
    public function changePosition($route, $el, $id, Request $request, $bundle = null)
    {
        if($bundle) {
            $repository = $this->getDoctrine()->getRepository('Akyos\\'.$bundle.'\Entity\\'.$el);
        } else {
            $repository = $this->getDoctrine()->getRepository('App\\Entity\\'.$el);
        }

        $entityOne = $repository->find($id);

        if ($entityOne->getPosition() < $request->get('position')) {
            for ($i = $entityOne->getPosition()+1; $i <= $request->get('position'); $i++) {
                $entityTwo = $repository->findOneBy(array('position' => $i));
                $entityTwo->setPosition($i-1);
            }
        } elseif ($entityOne->getPosition() > $request->get('position')) {
            for ($i = $request->get('position'); $i < $entityOne->getPosition(); $i++) {
                $entityTwo = $repository->findOneBy(array('position' => $i));
                $entityTwo->setPosition($i+1);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityOne->setPosition($request->get('position'));
        $entityManager->flush();

        return $this->redirectToRoute($route.'_index');
    }

//    /**
//     * @Route("/reset-position/{route}/{el}", name="reset_position", methods={"POST"})
//     */
//    public function resetPosition($route, $el, Request $request)
//    {
//        $entity = $this->getDoctrine()->getRepository('Akyos\CoreBundle\Entity\\'.$el);
//
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityOne->setPosition($request->get('position'));
//        $entityManager->flush();
//
//        return $this->redirectToRoute($route.'_index');
//    }

    /**
     * @Route("/change-status/{route}/{el}/{id}/{bundle}", name="change_status", methods={"POST"})
     * @param $route
     * @param $el
     * @param $id
     * @param $bundle
     * @param Request $request
     * @return RedirectResponse
     */
    public function changeStatus($route, $el, $id, $bundle = null, Request $request)
    {
        if($bundle) {
            $repository = $this->getDoctrine()->getRepository('Akyos\\'.$bundle.'\Entity\\'.$el)->find($id);
        } else {
            $repository = $this->getDoctrine()->getRepository('App\Entity\\'.$el)->find($id);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $repository->setPublished(!$repository->getPublished());
        $entityManager->flush();

        return $this->redirectToRoute($route.'_index');
    }

    public function sidebar($route)
    {
        return $this->render('@AkyosCore/layout/sidebar.html.twig', [
            'route' => $route
        ]);
    }

    public function renderMenu($menu, $page)
    {
        $menuArea = $this->getDoctrine()->getRepository(MenuArea::class)->findOneBy(['slug' => $menu]);
        return $this->renderView('@AkyosCore/menu/render.html.twig', [
            'menu' => $menuArea,
            'currentPage' => $page,
        ]);
    }
}
