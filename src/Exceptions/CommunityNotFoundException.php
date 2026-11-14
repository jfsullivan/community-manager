<?php

namespace jfsullivan\CommunityManager\Exceptions;

use RuntimeException;

class CommunityNotFoundException extends RuntimeException
{
    public static function for(mixed $value): self
    {
        return new self(sprintf('No community matches %s.', var_export($value, true)));
    }
}
