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
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $container->setParameter('user_roles', $config['user_roles']);
        // Twilio SMS
        $container->setParameter('twilio_accountSID', $config['twilio_accountSID']);
        $container->setParameter('twilio_authToken', $config['twilio_authToken']);
        $container->setParameter('twilio_sender', $config['twilio_sender']);
        // Mailjet SMS
        $container->setParameter('mailjet_smsToken', $config['mailjet_smsToken']);
        $container->setParameter('mailjet_sender', $config['mailjet_sender']);
        // Mailjet SMS
        $container->setParameter('mailjet_apiKey', $config['mailjet_apiKey']);
        $container->setParameter('mailjet_secretKey', $config['mailjet_secretKey']);
        // Google API
        $container->setParameter('google_apiKey', $config['google_apiKey']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->loadFromExtension('twig', array(
            'paths' => array(
                'lib/CoreBundle/Resources/views/bundles/TwigBundle/' => 'Twig',
            ),
        ));
    }
}