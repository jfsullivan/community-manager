<?php

namespace jfsullivan\CommunityManager\Tests\Fixtures;

use jfsullivan\CommunityManager\Testing\InteractsWithCommunityContext;
use jfsullivan\CommunityManager\Tests\TestCase;

/** A PHPUnit-style test case using the context helpers (the Pest suite applies them with uses()). */
abstract class ContextHelpersTestCase extends TestCase
{
    use InteractsWithCommunityContext;
}
