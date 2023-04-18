<?php

namespace Akyos\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CoreBundleExtension extends Extension implements PrependExtensionInterface
{
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = $this->getConfiguration($configs, $container);
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__ . '/../Resources/config')
		);
		$loader->load('services.yaml');
		
		foreach ($config as $key => $value) {
			$container->setParameter($key, $value);
		}
	}
	
    public function prepend(ContainerBuilder $container)
    {
        $container->loadFromExtension('twig', ['paths' => [__DIR__ . '/../Resources/views/bundles/TwigBundle/' => 'Twig',],]);
    }
}
