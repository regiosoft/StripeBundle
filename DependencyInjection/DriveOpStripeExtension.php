<?php

namespace DriveOp\StripeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use InvalidArgumentException;
use Exception;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DriveOpStripeExtension extends Extension
{

    /**
     * {@inheritDoc}
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $parameters = [
            'stripe_private_key'
        ];

        foreach ($parameters as $parameter) {
            if (!isset($config[Configuration::APP_NAME][$parameter])) {
                throw new InvalidArgumentException(
                    'The option "'.Configuration::ROOT_NAME.'.'.Configuration::APP_NAME.'.'.$parameter.'" must be set.'
                );
            }

            $container->setParameter(
                Configuration::ROOT_NAME.'.'.Configuration::APP_NAME.'.'.$parameter,
                $config[Configuration::APP_NAME][$parameter]
            );
        }
    }

    /**
     * {@inheritdoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getAlias()
    {
        return Configuration::ROOT_NAME.'_'.Configuration::APP_NAME;
    }
}
