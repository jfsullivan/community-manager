<?php

namespace jfsullivan\CommunityManager\Integrations\Spatie;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Context\CommunityContext;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * A spatie/laravel-multitenancy switch task (multitenancy.switch_tenant_tasks)
 * that makes Spatie's current tenant the community context's current
 * community, so code calling $tenant->makeCurrent(), Spatie's tenant finder
 * and Spatie's tenant-aware jobs all land in the same place as
 * CommunityContext::run(). Ignores the echo while the context is switching.
 */
class SyncCommunityContextTask implements SwitchTenantTask
{
    /** True while Spatie is driving a switch, so SyncSpatieTenant doesn't echo it back. */
    public static bool $syncing = false;

    public function __construct(protected CommunityContext $context) {}

    public function makeCurrent(IsTenant $tenant): void
    {
        if ($this->context->isSwitching() || ! $tenant instanceof Model) {
            return;
        }

        $communityClass = $this->context->communityModel();

        if (! $tenant instanceof $communityClass) {
            return;
        }

        static::$syncing = true;

        try {
            $this->context->set($tenant);
        } finally {
            static::$syncing = false;
        }
    }

    public function forgetCurrent(): void
    {
        if ($this->context->isSwitching()) {
            return;
        }

        static::$syncing = true;

        try {
            $this->context->forget();
        } finally {
            static::$syncing = false;
        }
    }
}
