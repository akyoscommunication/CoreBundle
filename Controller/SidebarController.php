<?php

namespace Akyos\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SidebarController extends AbstractController
{
    public function getBundleSidebar($route)
    {
        $html = "";

        if (class_exists('Akyos\BuilderBundle\Service\ExtendSidebar'))
        {
            $response = $this->forward('Akyos\BuilderBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        if (class_exists('Akyos\FormBundle\AkyosFormBundle'))
        {
            $response = $this->forward('Akyos\FormBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }

    public function getCustomSidebar($route)
    {
        $html = "";

        if (class_exists('App\Services\ExtendSidebar'))
        {
            $response = $this->forward('App\Services\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }

    public function getOptionsSidebar($route)
    {
        $html = "";

        if (class_exists('Akyos\BuilderBundle\Service\ExtendSidebar'))
        {
            $response = $this->forward('Akyos\BuilderBundle\Service\ExtendSidebar::getOptionsTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }
}