<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Doctrine;

/**
 * @internal
 */
final readonly class MappedColumn
{
    public function __construct(
        public string $field,
        public PropertyPath $path,
        public bool $nullable,
    ) {
    }
}
