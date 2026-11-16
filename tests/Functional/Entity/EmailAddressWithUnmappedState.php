<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class EmailAddressWithUnmappedState
{
    public static string $label = 'email';

    /** @var array<string, string> */
    public array $cache = [];

    public function __construct(
        #[ORM\Column(nullable: true)]
        public ?string $address = null,
    ) {
    }
}
