<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

final class NullableEmbeddableListener
{
    private const POST_LOAD = ['class' => self::class, 'method' => 'postLoad'];

    /**
     * @var array<class-string, NullableEmbeddedProperties>
     */
    private array $nullableEmbeddedPropertiesByClass = [];

    /**
     * @throws \ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $metadata = $args->getClassMetadata();
        if ($this->isAttachedTo($metadata) || !NullableEmbeddedProperties::existIn($metadata)) {
            return;
        }

        $metadata->addEntityListener(Events::postLoad, self::POST_LOAD['class'], self::POST_LOAD['method']);
    }

    /**
     * @throws \ReflectionException
     */
    public function postLoad(object $entity, PostLoadEventArgs $args): void
    {
        $this->nullableEmbeddedPropertiesOf($entity, $args)->nullifyEmptyIn($entity);
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function isAttachedTo(ClassMetadata $metadata): bool
    {
        return \in_array(self::POST_LOAD, $metadata->entityListeners[Events::postLoad] ?? [], true);
    }

    /**
     * @throws \ReflectionException
     */
    private function nullableEmbeddedPropertiesOf(object $entity, PostLoadEventArgs $args): NullableEmbeddedProperties
    {
        return $this->nullableEmbeddedPropertiesByClass[$entity::class]
            ??= NullableEmbeddedProperties::of($args->getObjectManager()->getClassMetadata($entity::class));
    }
}
