<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\EmbeddedClassMapping;
use Doctrine\ORM\Mapping\FieldMapping;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\NullableEmbedded;

/**
 * @internal
 */
final readonly class MappedEmbeddedProperty
{
    /**
     * @param list<MappedColumn> $columns
     */
    private function __construct(
        public PropertyPath $ownerPath,
        public \ReflectionProperty $property,
        public array $columns,
    ) {
    }

    /**
     * Innermost first, so an inner embeddable is nulled before its owner is checked.
     *
     * @param ClassMetadata<object> $metadata
     *
     * @return list<self>
     *
     * @throws \ReflectionException
     */
    public static function allIn(ClassMetadata $metadata): array
    {
        $paths = array_map(strval(...), array_keys($metadata->embeddedClasses));
        usort($paths, static fn (string $first, string $second): int => substr_count($second, '.') <=> substr_count($first, '.'));

        return array_map(static fn (string $path): self => self::at($metadata, $path), $paths);
    }

    /**
     * Readonly properties and NOT NULL columns may be deliberate, so they are left as Doctrine loads them.
     */
    public function canBeSetToNull(): bool
    {
        return $this->isNullable() && !$this->property->isReadOnly() && $this->hasOnlyNullableColumns();
    }

    private function isNullable(): bool
    {
        $type = $this->property->getType();
        if (null === $type) {
            return [] !== $this->property->getAttributes(NullableEmbedded::class);
        }

        return $type->allowsNull();
    }

    private function hasOnlyNullableColumns(): bool
    {
        foreach ($this->columns as $column) {
            if (!$column->nullable) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws \ReflectionException
     */
    private static function at(ClassMetadata $metadata, string $path): self
    {
        $segments = explode('.', $path);
        $fieldName = array_pop($segments);
        $ownerPath = PropertyPath::root();
        $ownerClass = $metadata->getReflectionClass();
        foreach ($segments as $depth => $segment) {
            $ownerPath = $ownerPath->then(self::property($ownerClass, $segment));
            $ownerClass = self::embeddableClass($metadata, implode('.', \array_slice($segments, 0, $depth + 1)));
        }

        return new self($ownerPath, self::property($ownerClass, $fieldName), self::columns($metadata, $path));
    }

    /**
     * @param \ReflectionClass<covariant object> $class
     *
     * @throws \ReflectionException
     */
    private static function property(\ReflectionClass $class, string $name): \ReflectionProperty
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
    private static function embeddableClass(ClassMetadata $metadata, string $path): \ReflectionClass
    {
        $mapping = $metadata->embeddedClasses[$path];

        return new \ReflectionClass($mapping instanceof EmbeddedClassMapping ? $mapping->class : $mapping['class']);
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @return list<MappedColumn>
     *
     * @throws \ReflectionException
     */
    private static function columns(ClassMetadata $metadata, string $path): array
    {
        $columns = [];
        foreach ($metadata->fieldMappings as $field => $mapping) {
            if (str_starts_with($field, $path.'.')) {
                $columns[] = new MappedColumn($field, self::columnPath($metadata, $path, $field), self::isNullableColumn($mapping));
            }
        }

        return $columns;
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws \ReflectionException
     */
    private static function columnPath(ClassMetadata $metadata, string $path, string $field): PropertyPath
    {
        $columnPath = PropertyPath::root();
        $class = self::embeddableClass($metadata, $path);
        foreach (explode('.', substr($field, \strlen($path) + 1)) as $segment) {
            $columnPath = $columnPath->then(self::property($class, $segment));
            $path .= '.'.$segment;
            if (isset($metadata->embeddedClasses[$path])) {
                $class = self::embeddableClass($metadata, $path);
            }
        }

        return $columnPath;
    }

    /**
     * @param FieldMapping|array{nullable?: bool} $mapping
     */
    private static function isNullableColumn(FieldMapping|array $mapping): bool
    {
        return true === ($mapping instanceof FieldMapping ? $mapping->nullable : ($mapping['nullable'] ?? false));
    }
}
