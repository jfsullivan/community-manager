<?php

namespace jfsullivan\CommunityManager\Tests;

/**
 * Boots the package the way an app that only uses the community core does:
 * community-manager.routes.enabled off, so none of the package's pages are
 * registered.
 */
class CoreOnlyTestCase extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config()->set('community-manager.routes.enabled', false);
    }
}
