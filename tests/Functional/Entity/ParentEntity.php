<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['parent' => ParentEntity::class, 'child' => ChildEntity::class, 'childWithListener' => ChildWithOwnEntityListener::class])]
class ParentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Embedded(class: EmailAddress::class)]
    private ?EmailAddress $emailAddress = null;

    public function getEmailAddress(): ?EmailAddress
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?EmailAddress $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }
}
