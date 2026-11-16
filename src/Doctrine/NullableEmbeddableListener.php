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
        $metadata = $args->getObjectManager()->getClassMetadata($entity::class);
        $entityClass = $metadata->getReflectionClass();
        if (!$entityClass->getAttributes(ContainsNullableEmbeddable::class)) {
            return;
        }

        $this->processEmbeddables($entity, $entityClass, $metadata->embeddedClasses);
    }

    /**
     * @param \ReflectionClass<object>            $entityClass
     * @param array<string, EmbeddedClassMapping> $embeddedClasses
     *
     * @throws \ReflectionException
     */
    private function processEmbeddables(object $entity, \ReflectionClass $entityClass, array $embeddedClasses): void
    {
        $paths = array_map(strval(...), array_keys($embeddedClasses));
        usort($paths, static fn (string $first, string $second): int => substr_count($second, '.') <=> substr_count($first, '.'));

        foreach ($paths as $path) {
            $ownerPath = explode('.', $path);
            $fieldName = array_pop($ownerPath);
            $owner = $this->resolveOwner($entity, $entityClass, $ownerPath);
            if (null === $owner) {
                continue;
            }

            $property = $owner === $entity ? $entityClass->getProperty($fieldName) : new \ReflectionProperty($owner, $fieldName);
            if (!$this->isNullable($property)) {
                continue;
            }

            /** @var object|null $value */
            $value = $property->getValue($owner);
            if (null === $value) {
                continue;
            }

            if ($this->allPropertiesAreNull($value)) {
                $property->setValue($owner, null);
            }
        }
    }

    /**
     * @param \ReflectionClass<object> $entityClass
     * @param list<string>             $ownerPath
     *
     * @throws \ReflectionException
     */
    private function resolveOwner(object $entity, \ReflectionClass $entityClass, array $ownerPath): ?object
    {
        $owner = $entity;
        $ownerClass = $entityClass;
        foreach ($ownerPath as $fieldName) {
            /** @var object|null $owner */
            $owner = $ownerClass->getProperty($fieldName)->getValue($owner);
            if (null === $owner) {
                return null;
            }
            $ownerClass = new \ReflectionObject($owner);
        }

        return $owner;
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
