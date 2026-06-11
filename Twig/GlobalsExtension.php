<?php

declare(strict_types=1);

namespace Akyos\CoreBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return [];
    }
}
