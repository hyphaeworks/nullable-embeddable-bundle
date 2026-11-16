<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @internal
 */
final readonly class NullableEmbeddedProperties
{
    /**
     * @param list<NullableEmbeddedProperty> $properties
     */
    private function __construct(
        private array $properties,
    ) {
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws \ReflectionException
     */
    public static function of(ClassMetadata $metadata): self
    {
        return new self(array_values(array_filter(array_map(
            NullableEmbeddedProperty::of(...),
            MappedEmbeddedProperty::allIn($metadata),
        ))));
    }

    /**
     * @param ClassMetadata<object> $metadata
     *
     * @throws \ReflectionException
     */
    public static function existIn(ClassMetadata $metadata): bool
    {
        foreach (MappedEmbeddedProperty::allIn($metadata) as $embedded) {
            if ($embedded->canBeSetToNull()) {
                return true;
            }
        }

        return false;
    }

    public function nullifyEmptyIn(object $entity): void
    {
        foreach ($this->properties as $property) {
            $property->nullifyIfEmpty($entity);
        }
    }
}
