<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EntityWithStrictEmailAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Embedded(class: StrictEmailAddress::class)]
    private ?StrictEmailAddress $emailAddress = null;

    public function getEmailAddress(): ?StrictEmailAddress
    {
        return $this->emailAddress;
    }
}
