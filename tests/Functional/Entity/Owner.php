<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Owner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(cascade: ['persist'])]
        #[ORM\JoinColumn(nullable: false)]
        private AttributesTestEntity $owned,
    ) {
    }

    public function getOwned(): AttributesTestEntity
    {
        return $this->owned;
    }
}
