<?php

namespace jfsullivan\CommunityManager\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Exceptions\CommunityMismatchException;
use jfsullivan\CommunityManager\Exceptions\MissingCommunityException;
use jfsullivan\CommunityManager\Isolation\CommunityScope;

/**
 * Strict per-community isolation for a model with a community column.
 *
 * - Queries only see the current community's rows ({@see CommunityScope}).
 * - A new row takes the current community when its column is empty; with no
 *   current community it is refused (MissingCommunityException) unless the
 *   model allows rows without one.
 * - While a community is current, saving or deleting another community's row
 *   (or moving a row between communities) throws CommunityMismatchException.
 *   Wrap deliberate cross-community writes in static::withoutCommunityGuard().
 *
 * Per-model options (declare on the model; none are required):
 * - `protected string $communityColumn = 'team_id';`
 * - `protected bool $sharesCommunityRows = true;` rows with no community
 *   (system rows) are visible inside every community.
 * - `protected string $whenNoCommunity = 'unowned';` with no current
 *   community: 'none' (nothing), 'unowned' (only rows with no community) or
 *   'all'. Defaults to config('community-manager.isolation.when_no_community'),
 *   or 'unowned' for a model that shares rows.
 *
 * Mass updates and deletes through the query builder are scoped too; raw
 * DB::table() queries, insert() and saveQuietly() bypass the model events.
 *
 * @method static Builder<static> withoutCommunityScope()
 * @method static Builder<static> forCommunity(mixed $community)
 */
trait BelongsToCommunity
{
    public static function bootBelongsToCommunity(): void
    {
        static::addGlobalScope(new CommunityScope);

        static::saving(function (self $model) {
            if (! $model->exists) {
                $model->fillCommunityFromContext();
            }

            $model->guardCommunityWrite('save');
        });

        static::deleting(function (self $model) {
            $model->guardCommunityWrite('delete');
        });
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(config('community-manager.community_model'), $this->getCommunityColumn());
    }

    public function getCommunityColumn(): string
    {
        return property_exists($this, 'communityColumn') && is_string($this->communityColumn)
            ? $this->communityColumn
            : config('community-manager.isolation.column', 'community_id');
    }

    public function allowsSharedCommunityRows(): bool
    {
        return property_exists($this, 'sharesCommunityRows') && $this->sharesCommunityRows === true;
    }

    /** What a scoped query returns with no current community: 'none', 'unowned' or 'all'. */
    public function whenNoCommunity(): string
    {
        if (property_exists($this, 'whenNoCommunity') && is_string($this->whenNoCommunity)) {
            return $this->whenNoCommunity;
        }

        return $this->allowsSharedCommunityRows()
            ? 'unowned'
            : config('community-manager.isolation.when_no_community', 'none');
    }

    /** Whether the row belongs to the current community. */
    public function belongsToCurrentCommunity(): bool
    {
        $currentId = app(CommunityContext::class)->id();

        return $currentId !== null && (string) $this->getAttribute($this->getCommunityColumn()) === (string) $currentId;
    }

    /**
     * Run the callback with the write guard (and the create-without-community
     * check) off, for deliberate cross-community writes.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function withoutCommunityGuard(Closure $callback): mixed
    {
        return app(CommunityContext::class)->withoutGuard($callback);
    }

    /** Fill an empty community column from the context, or refuse a row with no community. */
    public function fillCommunityFromContext(): void
    {
        $column = $this->getCommunityColumn();

        if ($this->getAttribute($column) !== null) {
            return;
        }

        $context = app(CommunityContext::class);

        if ($communityId = $context->id()) {
            $this->setAttribute($column, $communityId);

            return;
        }

        if ($this->allowsSharedCommunityRows()
            || $this->whenNoCommunity() === 'unowned'
            || $context->guardIsSuspended()
            || ! config('community-manager.isolation.require_community_on_create', true)) {
            return;
        }

        throw MissingCommunityException::forModel($this, $column);
    }

    /** @throws CommunityMismatchException */
    public function guardCommunityWrite(string $action): void
    {
        $context = app(CommunityContext::class);

        if (! config('community-manager.isolation.guard_writes', true) || $context->guardIsSuspended()) {
            return;
        }

        $currentId = $context->id();

        if ($currentId === null) {
            return;
        }

        $column = $this->getCommunityColumn();
        $values = $action === 'delete'
            ? [$this->getRawOriginal($column)]
            : [$this->getAttribute($column), $this->exists ? $this->getRawOriginal($column) : $this->getAttribute($column)];

        foreach ($values as $value) {
            if ($value === null || (string) $value !== (string) $currentId) {
                throw CommunityMismatchException::forModel($this, $action, $value, $currentId);
            }
        }
    }
}
