<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

/**
 * @internal
 */
final readonly class NullableEmbeddedProperty
{
    /**
     * @param list<PropertyPath> $columnPaths
     */
    private function __construct(
        private PropertyPath $ownerPath,
        private \ReflectionProperty $property,
        private array $columnPaths,
    ) {
    }

    public static function of(MappedEmbeddedProperty $embedded): ?self
    {
        if (!$embedded->canBeSetToNull()) {
            return null;
        }

        return new self(
            $embedded->ownerPath,
            $embedded->property,
            array_map(static fn (MappedColumn $column): PropertyPath => $column->path, $embedded->columns),
        );
    }

    public function nullifyIfEmpty(object $entity): void
    {
        $owner = $this->ownerPath->readFrom($entity);
        if (!\is_object($owner)) {
            return;
        }

        $embeddable = $this->property->getValue($owner);
        if (!\is_object($embeddable) || $this->holdsValue($embeddable)) {
            return;
        }

        $this->property->setValue($owner, null);
    }

    private function holdsValue(object $embeddable): bool
    {
        foreach ($this->columnPaths as $columnPath) {
            if (null !== $columnPath->readFrom($embeddable)) {
                return true;
            }
        }

        return false;
    }
}
