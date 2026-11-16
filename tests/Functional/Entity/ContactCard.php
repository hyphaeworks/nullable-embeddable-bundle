<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class ContactCard
{
    public function __construct(
        #[ORM\Embedded(class: EmailAddress::class)]
        public EmailAddress $emailAddress = new EmailAddress(),
    ) {
    }
}
