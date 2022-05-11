<?php

namespace Akyos\CoreBundle;

use Akyos\CoreBundle\DependencyInjection\CoreBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkyosCoreBundle extends Bundle
{
	public function getContainerExtension(): ?ExtensionInterface
	{
		if (null === $this->extension) {
			$this->extension = new CoreBundleExtension();
		}
		return $this->extension;
	}
}
