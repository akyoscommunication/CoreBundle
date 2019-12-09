<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    protected $coreOptionsRepository;

    public function __construct(CoreOptionsRepository $coreOptionsRepository)
    {
        $this->coreOptionsRepository = $coreOptionsRepository;
    }

    public function getGlobals(): array
    {
        $coreOptions = $this->coreOptionsRepository->findAll();
        if($coreOptions) {
           $coreOptions = $coreOptions[0];
        }
        return [
            'core_options' => $coreOptions
        ];
    }
}
