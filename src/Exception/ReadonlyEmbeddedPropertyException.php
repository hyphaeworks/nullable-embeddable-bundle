<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Exception;

final class ReadonlyEmbeddedPropertyException extends \LogicException
{
    public static function forProperty(\ReflectionProperty $property): self
    {
        return new self(\sprintf(
            '%s::$%s is readonly, so an empty embeddable cannot be set to null. Remove "readonly" from the property.',
            $property->class,
            $property->name,
        ));
    }
}
