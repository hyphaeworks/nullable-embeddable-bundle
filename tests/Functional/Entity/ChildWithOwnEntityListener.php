<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\EntityListeners([ChildEntityListener::class])]
class ChildWithOwnEntityListener extends MarkedParentEntity
{
}
