<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle;

use Doctrine\ORM\Events;
use Hyphaeworks\NullableEmbeddableBundle\Doctrine\NullableEmbeddableListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class NullableEmbeddableBundle extends AbstractBundle
{
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set(NullableEmbeddableListener::class)
            ->tag('doctrine.event_listener', ['event' => Events::loadClassMetadata])
            ->tag('doctrine.orm.entity_listener');
    }
}
