<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\XmlEntity;

use Hyphaeworks\NullableEmbeddableBundle\Attribute\NullableEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EmailAddress;

class XmlTestEntity
{
    private ?int $id = null;
    /**
     * @var EmailAddress|null
     */
    #[NullableEmbedded]
    private $emailAddress;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return EmailAddress|null
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param EmailAddress|null $emailAddress
     */
    public function setEmailAddress($emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }
}
