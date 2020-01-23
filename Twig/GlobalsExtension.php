<?php

namespace Akyos\CoreBundle\Twig;

use Akyos\CoreBundle\Repository\CoreOptionsRepository;
use Akyos\CoreBundle\Repository\RgpdOptionsRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    protected $coreOptionsRepository;
    protected $rgpdOptionsRepository;

    public function __construct(CoreOptionsRepository $coreOptionsRepository, RgpdOptionsRepository $rgpdOptionsRepository)
    {
        $this->coreOptionsRepository = $coreOptionsRepository;
        $this->rgpdOptionsRepository = $rgpdOptionsRepository;
    }

    public function getGlobals(): array
    {
        $coreOptions = $this->coreOptionsRepository->findAll();
        $rgpdOptions = $this->rgpdOptionsRepository->findAll();
        if($coreOptions) {
           $coreOptions = $coreOptions[0];
        }
        if($rgpdOptions) {
            $rgpdOptions = $rgpdOptions[0];
        }
        return [
            'core_options' => $coreOptions,
            'rgpd_options' => $rgpdOptions
        ];
    }
}
