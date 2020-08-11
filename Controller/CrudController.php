<?php

namespace Akyos\CoreBundle\Controller;

use Akyos\BuilderBundle\AkyosBuilderBundle;
use Akyos\BuilderBundle\Entity\BuilderOptions;
use Akyos\CoreBundle\Services\CoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CrudController extends AbstractController
{
    /** @var CoreService */
    private $coreService;

    public function __construct(CoreService $coreService)
    {
        $this->coreService = $coreService;
    }

    public function getBundleTab($objectType)
    {
        $html = "";
        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $objectType)) {
            $response = $this->forward('Akyos\BuilderBundle\Controller\BuilderController::getTab');
            $html .= $response->getContent();
        }

        return new Response($html);
    }

    public function getBundleTabContent($objectType, $objectId)
    {
        $html = "";

        if($this->coreService->checkIfBundleEnable(AkyosBuilderBundle::class, BuilderOptions::class, $objectType)) {
            $response = $this->forward('Akyos\\BuilderBundle\\Controller\\BuilderController::getTabContent', ['objectType' => $objectType, 'objectId' => $objectId]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }
}
