<?php

namespace jfsullivan\CommunityManager\Context\Tasks;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\SwitchesCommunity;
use jfsullivan\CommunityManager\Integrations\Spatie\SyncCommunityContextTask;
use Spatie\Multitenancy\Contracts\IsTenant;

/**
 * Keeps spatie/laravel-multitenancy's current tenant in step with the
 * community context: makes the community Spatie's current tenant when it
 * implements IsTenant, and forgets Spatie's tenant when the community is
 * forgotten. Pair it with Integrations\Spatie\SyncCommunityContextTask in
 * multitenancy.switch_tenant_tasks for the other direction.
 */
class SyncSpatieTenant implements SwitchesCommunity
{
    public function makeCurrent(Model $community): void
    {
        if (SyncCommunityContextTask::$syncing || ! $community instanceof IsTenant || $community->isCurrent()) {
            return;
        }

        $community->makeCurrent();
    }

    public function forgetCurrent(): void
    {
        if (SyncCommunityContextTask::$syncing || ! interface_exists(IsTenant::class) || ! app()->bound(IsTenant::class)) {
            return;
        }

        $tenantClass = app(IsTenant::class);

        $tenantClass::forgetCurrent();
    }
}
