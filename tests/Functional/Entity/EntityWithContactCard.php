<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EntityWithContactCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Embedded(class: ContactCard::class)]
        private ?ContactCard $contactCard = null,
    ) {
    }

    public function getContactCard(): ?ContactCard
    {
        return $this->contactCard;
    }
}
