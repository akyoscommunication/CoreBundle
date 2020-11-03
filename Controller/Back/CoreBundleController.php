<?php

namespace Akyos\CoreBundle\Controller\Back;

use Akyos\CoreBundle\Entity\MenuArea;
use Akyos\CoreBundle\Entity\MenuItem;
use Akyos\CoreBundle\Repository\MenuItemRepository;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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

    /**
     * permet de changer les positions des éléments enfant d'une entité,
     * doit prendre en paramètres :
     * route du parent
     * id de l'élement cible + namespace complet ("Akyos\\CoreBundle\\Entity\\PostDocument")
     * id du parent + namespace complet "Akyos\\CoreBundle\\Entity\\Post"
     * @Route("/change-position-sub/{route}/{id}/{namespace}/{parentId}/{namespaceParent}/{tab}", name="change_position_sub", methods={"POST"})
     * @param $route
     * @param $id
     * @param $namespace
     * @param Request $request
     * @param $parentId
     * @param $namespaceParent
     * @param null $tab
     * @return RedirectResponse
     */
    public function changePositionSub($route, $id, $namespace, Request $request, $parentId = null, $namespaceParent = null, $tab = null)
    {
        $repository = $this->getDoctrine()->getRepository($namespace);
        $entityOne = $repository->findOneById($id);
        $oldPosition = $entityOne->getPosition();
        $newPosition = $request->get('position');
        if($parentId && $namespaceParent){
            //Pour appeler la collection d'éléments depuis le parent à partir du nom de l'entité mise en param
            $array = explode('\\', $namespace);
            $command = 'get'.array_pop($array).'s';
            $repositoryParent = $this->getDoctrine()->getRepository($namespaceParent);
            $els = $repositoryParent->findOneById($parentId)->$command();
        }else{
            $els = $repository->findAll();
        }
        if($oldPosition < $newPosition){
            foreach ($els as $item) {
                if($item->getPosition() > $oldPosition && $item->getPosition() <= $newPosition){
                    $item->setPosition($item->getPosition()-1);
                }
            }
        }elseif($oldPosition > $newPosition){
            foreach ($els as $item) {
                if($item->getPosition() >= $newPosition && $item->getPosition() < $oldPosition){
                    $item->setPosition($item->getPosition()+1);
                }
            }
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityOne->setPosition($request->get('position'));
        $entityManager->flush();
        if($parentId && $namespaceParent){
            return $this->redirectToRoute($route.'_edit', [
                'id'=>$parentId,
                'tab'=>$tab
            ]);
        }else{
            return $this->redirectToRoute($route.'_index');
        }
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

    /**
     * @Route("/clear-cache", name="clear_cache", methods={"GET"})
     * @param string $env
     * @param bool $debug
     * @return RedirectResponse
     * @throws \Exception
     */
    public function clearCache($env = 'prod', $debug = false)
    {
        $kernel = new Kernel($env, $debug);
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'cache:clear'
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $this->addFlash('success', 'Le cache serveur a bien été vidé.');
        return $this->redirectToRoute('core_index');
    }
}
