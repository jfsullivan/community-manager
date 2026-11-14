<?php

namespace jfsullivan\CommunityManager\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/** A community-owned row created with no community set and none current. */
class MissingCommunityException extends RuntimeException
{
    public static function forModel(Model $model, string $column): self
    {
        return new self(sprintf(
            'Refusing to create %s without a community: set %s or create it inside CommunityContext::run().',
            $model::class,
            $column,
        ));
    }
}
