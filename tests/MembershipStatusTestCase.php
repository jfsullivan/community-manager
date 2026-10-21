<?php

namespace jfsullivan\CommunityManager\Tests;

/**
 * Boots the package with community-manager.membership_status.enabled on, so
 * the provider registers the status page route itself, the way an app gets it.
 */
class MembershipStatusTestCase extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config()->set('community-manager.membership_status.enabled', true);
    }
}
