<?php

namespace Akyos\CoreBundle\Controller;

use App\Kernel;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="core_")
 */
class CoreBundleController extends AbstractController
{
	/**
	 * @Route("/change-position/{route}/{el}/{id}/{bundle}", name="change_position", methods={"POST"})
	 * @param $route
	 * @param $el
	 * @param $id
	 * @param Request $request
	 * @param $bundle
	 * @return RedirectResponse
	 */
	public function changePosition($route, $el, $id, Request $request, $bundle = null): RedirectResponse
	{
		if ($bundle) {
			$repository = $this->getDoctrine()->getRepository('Akyos\\' . $bundle . '\Entity\\' . $el);
		} else {
			$repository = $this->getDoctrine()->getRepository('App\\Entity\\' . $el);
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
		
		$entityManager = $this->getDoctrine()->getManager();
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
	public function changePositionSub($route, $id, $namespace, Request $request, $parentId = null, $namespaceParent = null, $tab = null): RedirectResponse
	{
		$repository = $this->getDoctrine()->getRepository($namespace);
		$entityOne = $repository->find($id);
		$oldPosition = $entityOne->getPosition();
		$newPosition = $request->get('position');
		if ($parentId && $namespaceParent) {
			//Pour appeler la collection d'éléments depuis le parent à partir du nom de l'entité mise en param
			$array = explode('\\', $namespace);
			$command = 'get' . array_pop($array) . 's';
			$repositoryParent = $this->getDoctrine()->getRepository($namespaceParent);
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
		$entityManager = $this->getDoctrine()->getManager();
		$entityOne->setPosition($request->get('position'));
		$entityManager->flush();
		if ($parentId && $namespaceParent) {
			return $this->redirectToRoute($route . '_edit', [
				'id' => $parentId,
				'tab' => $tab
			]);
		}

        return $this->redirectToRoute($route . '_index');
    }
	
	/**
	 * @Route("/change-status/{redirect}/{entity}/{id}", name="change_status", methods={"POST"})
	 * @param $redirect
	 * @param $entity
	 * @param $id
	 * @return RedirectResponse
	 */
	public function changeStatus($redirect, $entity, $id): RedirectResponse
	{
		$el = $this->getDoctrine()->getRepository($entity)->find($id);
		
		$entityManager = $this->getDoctrine()->getManager();
		if (property_exists($el, 'published')) {
			$el->setPublished(!$el->getPublished());
		}
		$entityManager->flush();
		
		return $this->redirect(urldecode($redirect));
	}

    /**
     * @Route("/clear-cache", name="clear_cache", methods={"GET"})
     * @param string $env
     * @param bool $debug
     * @param string $returnRoute
     * @return RedirectResponse
     * @throws Exception
     */
	public function clearCache(string $env = 'prod', bool $debug = false, string $returnRoute = 'cms_index'): RedirectResponse
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
		return $this->redirectToRoute($returnRoute);
	}

    /**
     * @Route("/restart-messenger", name="restart_messenger", methods={"GET"})
     * @param string $env
     * @param bool $debug
     * @param string $returnRoute
     * @return RedirectResponse
     * @throws Exception
     */
	public function restartMessenger(string $env = 'prod', bool $debug = false, string $returnRoute = 'cms_index'): RedirectResponse
	{
		$kernel = new Kernel($env, $debug);
		$application = new Application($kernel);
		$application->setAutoExit(false);
		$input = new ArrayInput([
			'command' => 'messenger:stop-workers'
		]);
		$output = new BufferedOutput();
		$application->run($input, $output);
		
		$this->addFlash('success', 'Les workers ont tous été stoppés.');
		return $this->redirectToRoute($returnRoute);
	}
}
