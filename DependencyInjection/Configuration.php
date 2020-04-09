<?php

namespace Akyos\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('core_bundle');

        return $treeBuilder
            ->getRootNode()
                ->children()
                ->arrayNode('recaptcha')
                    ->children()
                        ->scalarNode('public_key')->isRequired()->end()
                        ->scalarNode('private_key')->isRequired()->end()
                    ->end()
                ->end()
            ->end();
    }
}
