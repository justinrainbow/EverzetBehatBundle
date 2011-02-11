<?php

namespace Bundle\Everzet\BehatBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat extension for DIC.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatExtension extends Extension
{
    /**
     * Load behat configuration. 
     * 
     * @param   array               $config     configuration parameters
     * @param   ContainerBuilder    $container  service container
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.lib.path')) {
            $this->loadDefaults($container);
        }

        foreach (array('formatter', 'colors', 'locale') as $attribute) {
            if (isset($config[$attribute])) {
                if ('formatter' === $attribute) {
                    $container->setParameter('behat.formatter.name', $config[$attribute]);
                } else {
                    $container->setParameter('behat.formatter.' . $attribute, $config[$attribute]);
                }
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNamespace()
    {
        return 'http://everzet.com/schema/dic/behat';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'behat';
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadDefaults($container)
    {
        $reflection = new \ReflectionClass('Everzet\Behat\UniversalClassLoader');
        $container->setParameter('behat.lib.path', realpath(dirname($reflection->getFileName()) . '/../../../'));

        $loader = new XmlFileLoader($container, new FileLocator($container->getParameter('behat.lib.path') . '/src/Everzet/Behat/ServiceContainer'));
        $loader->load('container.xml');
        $loader->load(__DIR__ . '/../Resources/config/behat.xml');
    }
}
