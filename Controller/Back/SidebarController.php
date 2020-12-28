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
		foreach ($finder->directories()->in($this->getParameter('kernel.project_dir').'/lib') as $bundleDirectory) {
			if(class_exists('Akyos\\'.$bundleDirectory->getFilename().'\Service\ExtendSidebar')) {
				if(method_exists('Akyos\\'.$bundleDirectory->getFilename().'\Service\ExtendSidebar', 'getTemplate')) {
					$response = $this->forward('Akyos\\' . $bundleDirectory->getFilename() . '\Service\ExtendSidebar::getTemplate', ['route' => $route]);
					$html .= $response->getContent();
				}
			}
		}

        return new Response($html);
    }

    public function getCustomSidebar($route): Response
    {
        $html = "";

        if (class_exists('App\Services\ExtendSidebar'))
        {
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
		foreach ($finder->directories()->in($this->getParameter('kernel.project_dir').'/lib') as $bundleDirectory) {
			if(class_exists('Akyos\\'.$bundleDirectory->getFilename().'\Service\ExtendSidebar')) {
				if(method_exists('Akyos\\'.$bundleDirectory->getFilename().'\Service\ExtendSidebar', 'getOptionsTemplate')) {
					$response = $this->forward('Akyos\\' . $bundleDirectory->getFilename() . '\Service\ExtendSidebar::getOptionsTemplate', ['route' => $route]);
					$html .= $response->getContent();
				}
			}
		}

        return new Response($html);
    }
}
