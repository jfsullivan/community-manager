<?php

namespace jfsullivan\CommunityManager\Tests;

/**
 * Boots the package with community-manager.disabling.enabled on, so the
 * provider registers the pages and the web-group middleware itself (the way
 * an app gets them), rather than a test wiring them up afterwards.
 */
class DisablingTestCase extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config()->set('community-manager.disabling.enabled', true);
    }
}
