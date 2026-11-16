<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EntityWithReadonlyNestedEmbedded
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Embedded(class: PriceWithReadonlyContact::class)]
        private ?PriceWithReadonlyContact $price = null,
    ) {
    }

    public function getPrice(): ?PriceWithReadonlyContact
    {
        return $this->price;
    }
}
