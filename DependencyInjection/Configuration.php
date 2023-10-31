<?php

namespace Akyos\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('core_bundle');

        $treeBuilder->getRootNode()->children()
            // Nom du site utilisé notamment pour l'envoi des mails
            ->scalarNode('site_name')->defaultValue('Nouveau site Akyos')->end()
            // Email transport: "Symfony Mailer" ou "Mailjet API"
            ->scalarNode('email_transport')->defaultValue('Symfony Mailer')->end()
            // Set dev mode on "Mailjet API" to force usage of mailjet in dev mode
            ->scalarNode('email_dev_mode')->defaultValue('Symfony Mailer')->end()
            // SMS transport: "Mailjet SMS" ou "Twilio SMS
            ->scalarNode('sms_transport')->defaultValue('Nouveau site Akyos')->end()
            // ID pour l'utilisation du service Twilio
            ->scalarNode('twilio_accountSID')->defaultValue('ACe745711c080f8ac90bcac210adff34ed')->end()->scalarNode('twilio_authToken')->defaultValue('73c059f232dd7047e0b9a590f43556ae')->end()->scalarNode('twilio_sender')->defaultValue('+16513834762')->end()
            // ID pour l'utilisation du service Mailjet
            ->scalarNode('mailjet_smsToken')->defaultValue('e4e31c428d114e48a48b74190b90d6eb')->end()->scalarNode('mailjet_sender')->defaultValue('AkyosCom')->end()
            // ID pour l'utilisation du service Google
            ->scalarNode('google_apiKey')->defaultValue('AIzaSyD6-wd-qhuzRXUpNKNxHLFcXE9o8BKWYNQ')->end()->scalarNode('mailjet_apiKey')->defaultValue('da24bfa22137d3fb6e3cfe6bb3e5985c')->end()->scalarNode('mailjet_secretKey')->defaultValue('a978ccee43c6ff2e13cd96fb04614d13')->end()
            // ID pour l'utilisation du service Universign
            ->scalarNode('universign_user')->defaultValue('thomas@akyos.com')->end()->scalarNode('universign_password')->defaultValue('XFRdzu9Vs9J5cwe')->end()->scalarNode('universign_password_test')->defaultValue('XFRdzu9Vs9J5cwe')->end()->scalarNode('universign_mode')->defaultValue('prod')->end()->end();

        return $treeBuilder;
    }
}
