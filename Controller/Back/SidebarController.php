<?php

namespace Akyos\CoreBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

class SidebarController extends AbstractController
{
	public function getBundleSidebar($route): Response
	{
		$html = "";

		$finder = new Finder();
		$finder->depth('== 0');
		if (file_exists($this->getParameter('kernel.project_dir') . '/vendor/akyos')) {
            		foreach ($finder->directories()->in($this->getParameter('kernel.project_dir') . '/vendor/akyos') as $bundleDirectory) {
				$bundleName = str_replace(' ', '', ucwords(str_replace('-', ' ', $bundleDirectory->getFilename())));
				if (class_exists('Akyos\\' . $bundleName . '\Service\ExtendSidebar') && method_exists('Akyos\\' . $bundleName . '\Service\ExtendSidebar', 'getTemplate')) {
					$response = $this->forward('Akyos\\' . $bundleName . '\Service\ExtendSidebar::getTemplate', ['route' => $route]);
					$html .= $response->getContent();
				}
            		}
        	}

		return new Response($html);
	}

	public function getCustomSidebar($route): Response
	{
		$html = "";

		if (class_exists('App\Services\ExtendSidebar')) {
			$response = $this->forward('App\Services\ExtendSidebar::getTemplate', ['route' => $route]);
			$html .= $response->getContent();
		}

		return new Response($html);
	}

	public function getOptionsSidebar($route): Response
	{
		$html = "";

		$finder = new Finder();
		$finder->depth('== 0');
		if (file_exists($this->getParameter('kernel.project_dir') . '/vendor/akyos')) {
		    foreach ($finder->directories()->in($this->getParameter('kernel.project_dir') . '/vendor/akyos') as $bundleDirectory) {
			$bundleName = str_replace(' ', '', ucwords(str_replace('-', ' ', $bundleDirectory->getFilename())));
			if (class_exists('Akyos\\' . $bundleName . '\Service\ExtendSidebar') && method_exists('Akyos\\' . $bundleName . '\Service\ExtendSidebar', 'getOptionsTemplate')) {
			    $response = $this->forward('Akyos\\' . $bundleName . '\Service\ExtendSidebar::getOptionsTemplate', ['route' => $route]);
			    $html .= $response->getContent();
			}
		    }
		}

		return new Response($html);
	}
}
