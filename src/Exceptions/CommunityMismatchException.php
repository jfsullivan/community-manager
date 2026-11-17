<?php

namespace jfsullivan\CommunityManager\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/** A write to a row of a community other than the current one. */
class CommunityMismatchException extends RuntimeException
{
    public static function forModel(Model $model, string $action, mixed $rowCommunityId, mixed $currentCommunityId): self
    {
        return new self(sprintf(
            'Refusing to %s %s #%s: it belongs to community %s, but community %s is current. Wrap deliberate cross-community writes in %s::withoutCommunityGuard().',
            $action,
            $model::class,
            $model->getKey() ?? '(new)',
            var_export($rowCommunityId, true),
            var_export($currentCommunityId, true),
            $model::class,
        ));
    }
}
