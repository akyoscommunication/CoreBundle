<?php

namespace Akyos\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CrudController extends AbstractController
{
    public function getBundleTab($objectType)
    {
        $html = "";
        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => $objectType])->getContent() === "true") {
            $response = $this->forward('Akyos\BuilderBundle\Controller\BuilderController::getTab');
            $html .= $response->getContent();
        }

        return new Response($html);
    }

    public function getBundleTabContent($objectType, $objectId)
    {
        $html = "";

        if($this->forward('Akyos\\CoreBundle\\Controller\\CoreBundleController::checkIfBundleEnable', ['bundle' => 'builder', 'entity' => $objectType])->getContent() === "true") {
            $response = $this->forward('Akyos\\BuilderBundle\\Controller\\BuilderController::getTabContent', ['objectType' => $objectType, 'objectId' => $objectId]);
            $html .= $response->getContent();
        }

        return new Response($html);
    }
}
