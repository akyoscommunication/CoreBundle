<?php

namespace Akyos\CoreBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SidebarController extends AbstractController
{
    public function getBundleSidebar($route): Response
    {
        $html = "";

        if (class_exists('Akyos\BuilderBundle\AkyosBuilderBundle'))
        {
            $response = $this->forward('Akyos\BuilderBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        if (class_exists('Akyos\FormBundle\AkyosFormBundle'))
        {
            $response = $this->forward('Akyos\FormBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }
        if (class_exists('Akyos\FileManagerBundle\AkyosFileManagerBundle'))
        {
            $response = $this->forward('Akyos\FileManagerBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        if (class_exists('Akyos\ShopBundle\AkyosShopBundle'))
        {
            $response = $this->forward('Akyos\ShopBundle\Service\ExtendSidebar::getTemplate', ['route' => $route]);
            $html .= $response->getContent();
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

        if (class_exists('Akyos\BuilderBundle\Service\ExtendSidebar'))
        {
            $response = $this->forward('Akyos\BuilderBundle\Service\ExtendSidebar::getOptionsTemplate', ['route' => $route]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }
}
