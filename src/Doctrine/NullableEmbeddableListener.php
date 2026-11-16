<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping\EmbeddedClassMapping;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\ContainsNullableEmbeddable;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\NullableEmbedded;

final class NullableEmbeddableListener
{
    /**
     * @throws \ReflectionException
     */
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!(new \ReflectionClass($entity))->getAttributes(ContainsNullableEmbeddable::class)) {
            return;
        }

        $metadata = $args->getObjectManager()->getClassMetadata($entity::class);
        $this->processEmbeddables($entity, $metadata->embeddedClasses);
    }

    /**
     * @param array<string, EmbeddedClassMapping> $embeddedClasses
     *
     * @throws \ReflectionException
     */
    private function processEmbeddables(object $entity, array $embeddedClasses): void
    {
        /** @var string $fieldName */
        foreach (array_keys($embeddedClasses) as $fieldName) {
            $property = new \ReflectionProperty($entity, $fieldName);

            if (!$this->isNullable($property)) {
                continue;
            }

            /** @var object|null $value */
            $value = $property->getValue($entity);
            if (null === $value) {
                continue;
            }

            if ($this->allPropertiesAreNull($value)) {
                $property->setValue($entity, null);
            }
        }
    }

    private function isNullable(\ReflectionProperty $property): bool
    {
        $type = $property->getType();

        if (null === $type) {
            return !empty($property->getAttributes(NullableEmbedded::class));
        }

        return $type->allowsNull();
    }

    private function allPropertiesAreNull(object $object): bool
    {
        foreach ((new \ReflectionObject($object))->getProperties() as $property) {
            if ($property->isInitialized($object) && null !== $property->getValue($object)) {
                return false;
            }
        }

        return true;
    }
}
