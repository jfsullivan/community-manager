<?php

use Illuminate\Support\Facades\View;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\SpatieCommunity;

function spatieCommunity(): SpatieCommunity
{
    return SpatieCommunity::find(createCommunity()->id);
}

it('makes the context\'s community Spatie\'s current tenant', function () {
    $a = spatieCommunity();
    $b = spatieCommunity();

    CommunityContext::set($a);
    expect(SpatieCommunity::current()?->getKey())->toBe($a->id);

    CommunityContext::set($b);
    expect(SpatieCommunity::current()?->getKey())->toBe($b->id);

    CommunityContext::forget();
    expect(SpatieCommunity::current())->toBeNull();
});

it('follows Spatie when code switches tenants the Spatie way', function () {
    $a = spatieCommunity();
    $b = spatieCommunity();

    $a->makeCurrent();
    expect(CommunityContext::id())->toBe($a->id)
        ->and(View::shared('currentCommunity')?->getKey())->toBe($a->id);

    $b->makeCurrent();
    expect(CommunityContext::id())->toBe($b->id)
        ->and(View::shared('currentCommunity')?->getKey())->toBe($b->id);

    SpatieCommunity::forgetCurrent();
    expect(CommunityContext::id())->toBeNull()
        ->and(View::shared('currentCommunity'))->toBeNull();
});

it('keeps both in step through nested runs and execute()', function () {
    $a = spatieCommunity();
    $b = spatieCommunity();

    CommunityContext::run($a, function () use ($a, $b) {
        $b->execute(function () use ($b) {
            expect(CommunityContext::id())->toBe($b->id)
                ->and(SpatieCommunity::current()->getKey())->toBe($b->id);
        });

        expect(CommunityContext::id())->toBe($a->id)
            ->and(SpatieCommunity::current()->getKey())->toBe($a->id);
    });

    expect(CommunityContext::id())->toBeNull()
        ->and(SpatieCommunity::current())->toBeNull();
});

it('answers Community::current() through Spatie for a Spatie tenant model', function () {
    $a = spatieCommunity();

    CommunityContext::run($a, fn () => expect(SpatieCommunity::current()->is($a))->toBeTrue()
        ->and($a->isCurrent())->toBeTrue());
});
