<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class StrictEmailAddress
{
    public function __construct(
        #[ORM\Column(type: 'string', length: 255, nullable: true)]
        public readonly string $address,
    ) {
    }
}
