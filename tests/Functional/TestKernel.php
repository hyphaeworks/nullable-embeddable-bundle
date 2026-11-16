<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Hyphaeworks\NullableEmbeddableBundle\NullableEmbeddableBundle;

final class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new NullableEmbeddableBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
            ]);
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'driver' => 'pdo_sqlite',
                    'memory' => true,
                ],
                'orm' => [
                    'mappings' => [
                        'Attribute' => [
                            'type' => 'attribute',
                            'dir' => __DIR__.'/Entity',
                            'prefix' => 'Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity',
                            'is_bundle' => false,
                        ],
                        'Xml' => [
                            'type' => 'xml',
                            'dir' => __DIR__.'/config/xml',
                            'prefix' => 'Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\XmlEntity',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ]);
        });
    }
}
