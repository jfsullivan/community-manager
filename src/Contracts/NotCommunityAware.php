<?php

namespace jfsullivan\CommunityManager\Contracts;

/**
 * Marks a queued job that never inherits the community it was dispatched
 * in: it runs with no current community (e.g. a platform-wide job).
 */
interface NotCommunityAware {}
