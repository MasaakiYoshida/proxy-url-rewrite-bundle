<?php
/*
 * Copyright (c) 2014 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of PHPMentorsProxyURLRewriteBundle.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace PHPMentors\ProxyURLRewriteBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class PHPMentorsProxyURLRewriteExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $this->transformConfigToContainerParameters($config, $container);
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'phpmentors_proxy_url_rewrite';
    }

    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function transformConfigToContainerParameters(array $config, ContainerBuilder $container)
    {
        if ($config['enabled']) {
            $index = 0;
            foreach ($config['proxy_urls'] as $path => $proxyUrl) {
                $definition = new DefinitionDecorator('phpmentors_proxy_url_rewrite.proxy_url');
                $definition->setArguments(array($path, $proxyUrl));

                $serviceId = 'phpmentors_proxy_url_rewrite.proxy_url'.$index;
                $container->setDefinition($serviceId, $definition);
                $container->getDefinition('phpmentors_proxy_url_rewrite.proxy_url_collection')->addMethodCall('add', array($serviceId, new Reference($serviceId)));
                ++$index;
            }
        } else {
            $container->removeDefinition('phpmentors_proxy_url_rewrite.proxy_url_rewrite_listener');
        }
    }
}
