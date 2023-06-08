<?php

namespace Akyos\CoreBundle\Controller;

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin', name: 'core_')]
class CoreBundleController extends AbstractController
{
    /**
     * @param $route
     * @param $el
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param null $bundle
     * @return RedirectResponse
     */
    #[Route(path: '/change-position/{route}/{el}/{id}/{bundle}', name: 'change_position', requirements: ['route' => '.+'], methods: ['POST'])]
    public function changePosition($route, $el, $id, Request $request, EntityManagerInterface $entityManager, $bundle = null): RedirectResponse
    {
        if ($bundle) {
            $repository = $entityManager->getRepository('Akyos\\' . $bundle . '\Entity\\' . $el);
        } else {
            $repository = $entityManager->getRepository('App\\Entity\\' . $el);
        }
        $entityOne = $repository->find($id);
        if ($entityOne->getPosition() < $request->get('position')) {
            for ($i = $entityOne->getPosition() + 1; $i <= $request->get('position'); $i++) {
                $entityTwo = $repository->findOneBy(['position' => $i]);
                $entityTwo->setPosition($i - 1);
            }
        } elseif ($entityOne->getPosition() > $request->get('position')) {
            for ($i = $request->get('position'); $i < $entityOne->getPosition(); $i++) {
                $entityTwo = $repository->findOneBy(['position' => $i]);
                $entityTwo->setPosition($i + 1);
            }
        }
        $entityOne->setPosition($request->get('position'));
        $entityManager->flush();
        return $this->redirectToRoute($route . '_index');
    }

    /**
     * permet de changer les positions des éléments enfant d'une entité,
     * doit prendre en paramètres :
     * route du parent
     * id de l'élement cible + namespace complet ("Akyos\\BlogBundle\\Entity\\PostDocument")
     * id du parent + namespace complet "Akyos\\BlogBundle\\Entity\\Post"
     * @param $route
     * @param $id
     * @param $namespace
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param null $parentId
     * @param null $namespaceParent
     * @param null $tab
     * @return RedirectResponse
     */
    #[Route(path: '/change-position-sub/{route}/{id}/{namespace}/{parentId}/{namespaceParent}/{tab}', name: 'change_position_sub', requirements: ['route' => '.+'], methods: ['POST'])]
    public function changePositionSub($route, $id, $namespace, Request $request, EntityManagerInterface $entityManager, $parentId = null, $namespaceParent = null, $tab = null): RedirectResponse
    {
        $repository = $entityManager->getRepository($namespace);
        $entityOne = $repository->find($id);
        $oldPosition = $entityOne->getPosition();
        $newPosition = $request->get('position');
        if ($parentId && $namespaceParent) {
            //Pour appeler la collection d'éléments depuis le parent à partir du nom de l'entité mise en param
            $array = explode('\\', $namespace);
            $command = 'get' . array_pop($array) . 's';
            $repositoryParent = $entityManager->getRepository($namespaceParent);
            $els = $repositoryParent->find($parentId)->$command();
        } else {
            $els = $repository->findAll();
        }
        if ($oldPosition < $newPosition) {
            foreach ($els as $item) {
                if ($item->getPosition() > $oldPosition && $item->getPosition() <= $newPosition) {
                    $item->setPosition($item->getPosition() - 1);
                }
            }
        } elseif ($oldPosition > $newPosition) {
            foreach ($els as $item) {
                if ($item->getPosition() >= $newPosition && $item->getPosition() < $oldPosition) {
                    $item->setPosition($item->getPosition() + 1);
                }
            }
        }
        $entityOne->setPosition($request->get('position'));
        $entityManager->flush();
        if ($parentId && $namespaceParent) {
            return $this->redirectToRoute($route . '_edit', ['id' => $parentId, 'tab' => $tab]);
        }
        return $this->redirectToRoute($route . '_index');
    }

    /**
     * @param $redirect
     * @param $entity
     * @param $id
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    #[Route(path: '/change-status/{redirect}/{entity}/{id}', name: 'change_status', methods: ['POST'], requirements: ['redirect' => '.+'])]
    public function changeStatus($redirect, $entity, $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        $el = $entityManager->getRepository($entity)->find($id);
        if (property_exists($el, 'published')) {
            $el->setPublished(!$el->getPublished());
        }
        $entityManager->flush();
        return $this->redirect(urldecode($redirect));
    }

    /**
     * @param string $env
     * @param bool $debug
     * @param string $returnRoute
     * @return RedirectResponse
     * @throws Exception
     */
    #[Route(path: '/clear-cache', name: 'clear_cache', methods: ['GET'])]
    public function clearCache(string $env = 'prod', bool $debug = false, string $returnRoute = 'cms_index'): RedirectResponse
    {
        $kernel = new Kernel($env, $debug);
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'cache:clear']);
        $output = new BufferedOutput();
        $application->run($input, $output);
        $this->addFlash('success', 'Le cache serveur a bien été vidé.');
        return $this->redirectToRoute($returnRoute);
    }

    /**
     * @param string $env
     * @param bool $debug
     * @param string $returnRoute
     * @return RedirectResponse
     * @throws Exception
     */
    #[Route(path: '/restart-messenger', name: 'restart_messenger', methods: ['GET'])]
    public function restartMessenger(string $env = 'prod', bool $debug = false, string $returnRoute = 'cms_index'): RedirectResponse
    {
        $kernel = new Kernel($env, $debug);
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'messenger:stop-workers']);
        $output = new BufferedOutput();
        $application->run($input, $output);
        $this->addFlash('success', 'Les workers ont tous été stoppés.');
        return $this->redirectToRoute($returnRoute);
    }
}
