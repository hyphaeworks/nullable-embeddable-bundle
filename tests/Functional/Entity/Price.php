<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class Price
{
    public function __construct(
        #[ORM\Column(nullable: true)]
        public ?int $amount = null,
        #[ORM\Embedded(class: EmailAddress::class)]
        public ?EmailAddress $contact = null,
    ) {
    }
}
