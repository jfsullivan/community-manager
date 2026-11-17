<?php

namespace jfsullivan\CommunityManager\Context\Tasks;

use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;

/**
 * Shares the current community with every view as $currentCommunity
 * (context.view_key), restoring the earlier shared value when forgotten.
 */
class ShareCommunityWithViews implements SwitchesCommunity
{
    protected bool $captured = false;

    protected mixed $previous = null;

    public function __construct(protected Factory $views) {}

    public function makeCurrent(Model $community): void
    {
        if (! $this->captured) {
            $this->previous = $this->views->shared($this->key());
            $this->captured = true;
        }

        $this->views->share($this->key(), $community);
    }

    public function forgetCurrent(): void
    {
        if (! $this->captured) {
            return;
        }

        $this->views->share($this->key(), $this->previous);
        $this->captured = false;
        $this->previous = null;
    }

    protected function key(): string
    {
        return config('community-manager.context.view_key', 'currentCommunity');
    }
}
