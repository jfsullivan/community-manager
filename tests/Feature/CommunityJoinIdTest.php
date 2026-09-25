<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use jfsullivan\CommunityManager\Models\Community;

test('a new join id is eight digits and not already in use', function () {
    $existing = Community::factory()->create();

    $joinId = Community::newJoinId();

    expect($joinId)->toBeInt()->toBeBetween(10000000, 99999999)
        ->and($joinId)->not->toBe($existing->join_id);
});
