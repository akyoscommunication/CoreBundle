<?php

namespace Akyos\CoreBundle;

use Akyos\CoreBundle\DependencyInjection\CoreBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkyosCoreBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CoreBundleExtension();
    }
}