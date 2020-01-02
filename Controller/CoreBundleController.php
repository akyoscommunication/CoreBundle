<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuItemRepository;
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
     */
    public function changePosition($route, $el, $id, Request $request, $bundle)
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

    public function checkIfBundleEnable($bundle, $entity)
    {
        if (class_exists('Akyos\BuilderBundle\AkyosBuilderBundle')) {
            $builderOptions = $this->getDoctrine()->getRepository('Akyos\BuilderBundle\Entity\BuilderOptions')->findAll();
            if ($builderOptions) {
                if (preg_grep('/'.$entity.'$/i', $builderOptions[0]->getHasBuilderEntities())) {
                    return new Response("true");
                } else return new Response("false");
            } else return new Response("false");
        }
        return new Response("false");
    }

    public function checkIfSeoEnable($entity)
    {
        $coreOptions = $this->getDoctrine()->getRepository('Akyos\CoreBundle\Entity\CoreOptions')->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasSeoEntities())) {
                return new Response("true");
            } else return new Response("false");
        } else return new Response("false");
    }

    public function checkIfArchiveEnable($entity)
    {
        $coreOptions = $this->getDoctrine()->getRepository('Akyos\CoreBundle\Entity\CoreOptions')->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasArchiveEntities())) {
                return new Response("true");
            } else return new Response("false");
        } else return new Response("false");
    }

    public function checkIfSingleEnable($entity)
    {
        $coreOptions = $this->getDoctrine()->getRepository('Akyos\CoreBundle\Entity\CoreOptions')->findAll();
        if ($coreOptions) {
            if (preg_grep('/'.$entity.'$/i', $coreOptions[0]->getHasSingleEntities())) {
                return new Response("true");
            } else return new Response("false");
        } else return new Response("false");
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
