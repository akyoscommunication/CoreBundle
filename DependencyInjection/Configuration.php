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
		
		$treeBuilder
			->getRootNode()
				->children()
				->arrayNode('user_roles')
				->defaultValue([
					'Utilisateur' => 'ROLE_USER',
					'Admin' => 'ROLE_ADMIN',
					'Super Admin' => 'ROLE_SUPER_ADMIN',
					'Akyos' => 'ROLE_AKYOS',
				])
				->scalarPrototype()->end()
				->end()
				->scalarNode('twilio_accountSID')
					->defaultValue('ACe745711c080f8ac90bcac210adff34ed')
				->end()
				->scalarNode('twilio_authToken')
					->defaultValue('73c059f232dd7047e0b9a590f43556ae')
				->end()
				->scalarNode('twilio_sender')
					->defaultValue('+16513834762')
				->end()
				->scalarNode('mailjet_smsToken')
					->defaultValue('e4e31c428d114e48a48b74190b90d6eb')
				->end()
				->scalarNode('mailjet_sender')
					->defaultValue('AkyosCom')
				->end()
				->scalarNode('google_apiKey')
					->defaultValue('AIzaSyD6-wd-qhuzRXUpNKNxHLFcXE9o8BKWYNQ')
				->end()
				->scalarNode('mailjet_apiKey')
					->defaultValue('da24bfa22137d3fb6e3cfe6bb3e5985c')
				->end()
				->scalarNode('mailjet_secretKey')
					->defaultValue('a978ccee43c6ff2e13cd96fb04614d13')
				->end()
				->scalarNode('universign_user')
					->defaultValue('thomas@akyos.com')
				->end()
				->scalarNode('universign_password')
					->defaultValue('XFRdzu9Vs9J5cwe')
				->end()
				->scalarNode('universign_mode')
					->defaultValue('prod')
				->end()
			->end();
		
		return $treeBuilder;
	}
}