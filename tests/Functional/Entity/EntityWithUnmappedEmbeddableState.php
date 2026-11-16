<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\ContainsNullableEmbeddable;

#[ORM\Entity]
#[ContainsNullableEmbeddable]
class EntityWithUnmappedEmbeddableState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Embedded(class: EmailAddressWithUnmappedState::class)]
        private ?EmailAddressWithUnmappedState $emailAddress = null,
    ) {
    }

    public function getEmailAddress(): ?EmailAddressWithUnmappedState
    {
        return $this->emailAddress;
    }
}
