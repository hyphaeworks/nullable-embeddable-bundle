<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

/**
 * @internal
 */
final readonly class PropertyPath
{
    /**
     * @param list<\ReflectionProperty> $properties
     */
    private function __construct(
        private array $properties,
    ) {
    }

    public static function root(): self
    {
        return new self([]);
    }

    public function then(\ReflectionProperty $property): self
    {
        return new self([...$this->properties, $property]);
    }

    public function readFrom(object $object): mixed
    {
        $value = $object;
        foreach ($this->properties as $property) {
            if (!\is_object($value) || !$property->isInitialized($value)) {
                return null;
            }
            $value = $property->getValue($value);
        }

        return $value;
    }
}
