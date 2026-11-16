<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

/**
 * @internal
 */
final readonly class NullableEmbeddedProperty
{
    /**
     * @param list<\ReflectionProperty>       $ownerPath
     * @param list<list<\ReflectionProperty>> $columnPaths
     */
    public function __construct(
        private array $ownerPath,
        private \ReflectionProperty $property,
        private array $columnPaths,
    ) {
    }

    public function nullifyIfEmpty(object $entity): void
    {
        $owner = $entity;
        foreach ($this->ownerPath as $ownerProperty) {
            /** @var object|null $owner */
            $owner = $ownerProperty->getValue($owner);
            if (null === $owner) {
                return;
            }
        }

        /** @var object|null $embeddable */
        $embeddable = $this->property->getValue($owner);
        if (null === $embeddable) {
            return;
        }

        foreach ($this->columnPaths as $columnPath) {
            if ($this->hasValue($embeddable, $columnPath)) {
                return;
            }
        }

        $this->property->setValue($owner, null);
    }

    /**
     * @param list<\ReflectionProperty> $columnPath
     */
    private function hasValue(object $embeddable, array $columnPath): bool
    {
        $column = array_pop($columnPath);
        $owner = $embeddable;
        foreach ($columnPath as $property) {
            if (!$property->isInitialized($owner)) {
                return false;
            }
            /** @var object|null $owner */
            $owner = $property->getValue($owner);
            if (null === $owner) {
                return false;
            }
        }

        return null !== $column && $column->isInitialized($owner) && null !== $column->getValue($owner);
    }
}
