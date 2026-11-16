<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class CountryCode
{
    public function __construct(
        #[ORM\Column(length: 2)]
        public ?string $code = null,
    ) {
    }
}
