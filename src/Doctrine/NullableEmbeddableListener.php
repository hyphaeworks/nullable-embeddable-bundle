<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\EmbeddedClassMapping;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\ContainsNullableEmbeddable;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\NullableEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Exception\ReadonlyEmbeddedPropertyException;

final class NullableEmbeddableListener
{
    /**
     * @var array<string, list<NullableEmbeddedProperty>>
     */
    private array $nullableEmbeddedPropertiesByClass = [];

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $metadata = $args->getClassMetadata();
        $listener = ['class' => self::class, 'method' => 'postLoad'];
        if (!$this->isMarked($metadata->getReflectionClass())
            || \in_array($listener, $metadata->entityListeners[Events::postLoad] ?? [], true)) {
            return;
        }

        $metadata->addEntityListener(Events::postLoad, $listener['class'], $listener['method']);
    }

    /**
     * @throws \ReflectionException
     */
    public function postLoad(object $entity, PostLoadEventArgs $args): void
    {
        $nullableEmbeddedProperties = $this->nullableEmbeddedPropertiesByClass[$entity::class]
            ??= $this->analyse($args->getObjectManager()->getClassMetadata($entity::class));

        foreach ($nullableEmbeddedProperties as $nullableEmbeddedProperty) {
            $nullableEmbeddedProperty->nullifyIfEmpty($entity);
        }
    }

    /**
     * @param \ReflectionClass<covariant object> $class
     */
    private function isMarked(\ReflectionClass $class): bool
    {
        do {
            if ([] !== $class->getAttributes(ContainsNullableEmbeddable::class)) {
                return true;
            }
        } while (false !== $class = $class->getParentClass());

        return false;
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @return list<NullableEmbeddedProperty>
     *
     * @throws \ReflectionException
     */
    private function analyse(ClassMetadata $metadata): array
    {
        $paths = array_map(strval(...), array_keys($metadata->embeddedClasses));
        usort($paths, static fn (string $first, string $second): int => substr_count($second, '.') <=> substr_count($first, '.'));

        $nullableEmbeddedProperties = [];
        foreach ($paths as $path) {
            $ownerPath = [];
            $ownerClass = $metadata->getReflectionClass();
            $segments = explode('.', $path);
            $fieldName = array_pop($segments);
            foreach ($segments as $depth => $segment) {
                $ownerPath[] = $this->property($ownerClass, $segment);
                $ownerClass = $this->embeddableClass($metadata, implode('.', \array_slice($segments, 0, $depth + 1)));
            }

            $property = $this->property($ownerClass, $fieldName);
            if (!$this->isNullable($property)) {
                continue;
            }
            if ($property->isReadOnly()) {
                throw ReadonlyEmbeddedPropertyException::forProperty($property);
            }

            $nullableEmbeddedProperties[] = new NullableEmbeddedProperty(
                $ownerPath,
                $property,
                $this->columnPaths($metadata, $path),
            );
        }

        return $nullableEmbeddedProperties;
    }

    /**
     * @param \ReflectionClass<covariant object> $class
     *
     * @throws \ReflectionException
     */
    private function property(\ReflectionClass $class, string $name): \ReflectionProperty
    {
        while (!$class->hasProperty($name) && false !== $parentClass = $class->getParentClass()) {
            $class = $parentClass;
        }

        return $class->getProperty($name);
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @return \ReflectionClass<object>
     *
     * @throws \ReflectionException
     */
    private function embeddableClass(ClassMetadata $metadata, string $path): \ReflectionClass
    {
        $mapping = $metadata->embeddedClasses[$path];
        $className = $mapping instanceof EmbeddedClassMapping ? $mapping->class : $mapping['class'];

        return new \ReflectionClass($className);
    }

    private function isNullable(\ReflectionProperty $property): bool
    {
        $type = $property->getType();

        if (null === $type) {
            return [] !== $property->getAttributes(NullableEmbedded::class);
        }

        return $type->allowsNull();
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @return list<list<\ReflectionProperty>>
     *
     * @throws \ReflectionException
     */
    private function columnPaths(ClassMetadata $metadata, string $path): array
    {
        $columnPaths = [];
        foreach (array_keys($metadata->fieldMappings) as $field) {
            if (!str_starts_with($field, $path.'.')) {
                continue;
            }

            $class = $this->embeddableClass($metadata, $path);
            $segmentPath = $path;
            $columnPath = [];
            foreach (explode('.', substr($field, \strlen($path) + 1)) as $segment) {
                $columnPath[] = $this->property($class, $segment);
                $segmentPath .= '.'.$segment;
                if (isset($metadata->embeddedClasses[$segmentPath])) {
                    $class = $this->embeddableClass($metadata, $segmentPath);
                }
            }
            $columnPaths[] = $columnPath;
        }

        return $columnPaths;
    }
}
