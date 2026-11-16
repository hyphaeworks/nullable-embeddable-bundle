<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

/**
 * @internal
 */
final readonly class NullableEmbeddedProperty
{
    /**
     * @param list<\ReflectionProperty> $ownerPath
     * @param list<\ReflectionProperty> $embeddableProperties
     */
    public function __construct(
        private array $ownerPath,
        private \ReflectionProperty $property,
        private array $embeddableProperties,
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

        foreach ($this->embeddableProperties as $embeddableProperty) {
            if ($embeddableProperty->isInitialized($embeddable) && null !== $embeddableProperty->getValue($embeddable)) {
                return;
            }
        }

        $this->property->setValue($owner, null);
    }
}
