<?php

namespace jfsullivan\CommunityManager\Tests;

use jfsullivan\CommunityManager\Context\Tasks\ShareCommunityWithViews;
use jfsullivan\CommunityManager\Context\Tasks\SyncSpatieTenant;
use jfsullivan\CommunityManager\Integrations\Spatie\SyncCommunityContextTask;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\SpatieCommunity;
use Spatie\Multitenancy\MultitenancyServiceProvider;

/**
 * Boots spatie/laravel-multitenancy with the bridge both ways: the context
 * drives Spatie (SyncSpatieTenant) and Spatie drives the context
 * (SyncCommunityContextTask), the way CompleteHOA would run during a move
 * off apex-multitenancy.
 */
class SpatieTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [...parent::getPackageProviders($app), MultitenancyServiceProvider::class];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('multitenancy.tenant_model', SpatieCommunity::class);
        config()->set('multitenancy.tenant_finder', null);
        config()->set('multitenancy.switch_tenant_tasks', [SyncCommunityContextTask::class]);
        config()->set('multitenancy.queues_are_tenant_aware_by_default', false);

        parent::getEnvironmentSetUp($app);

        config()->set('community-manager.community_model', SpatieCommunity::class);
        config()->set('community-manager.context.tasks', [SyncSpatieTenant::class, ShareCommunityWithViews::class]);
    }
}
