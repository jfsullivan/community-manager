<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use jfsullivan\CommunityManager\Models\Community;

test('a new join id is six digits and not already in use', function () {
    $existing = Community::factory()->create();

    $joinId = Community::newJoinId();

    expect($joinId)->toBeInt()->toBeBetween(100000, 999999)
        ->and($joinId)->not->toBe($existing->join_id);
});
