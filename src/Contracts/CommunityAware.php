<?php

namespace jfsullivan\CommunityManager\Contracts;

/**
 * Marks a queued job that runs inside the community it was dispatched in,
 * when config('community-manager.queue.aware_by_default') is false.
 */
interface CommunityAware {}
