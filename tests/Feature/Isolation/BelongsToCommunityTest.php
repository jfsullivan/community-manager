<?php

use jfsullivan\CommunityManager\Exceptions\CommunityMismatchException;
use jfsullivan\CommunityManager\Exceptions\MissingCommunityException;
use jfsullivan\CommunityManager\Facades\CommunityContext;
use jfsullivan\CommunityManager\Testing\CommunityIsolation;
use jfsullivan\CommunityManager\Tests\Fixtures\IsolationSchema;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\Note;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\SharedTag;
use jfsullivan\CommunityManager\Tests\Fixtures\Models\UnscopedEntry;
use PHPUnit\Framework\AssertionFailedError;

beforeEach(function () {
    IsolationSchema::create();

    $this->a = createCommunity();
    $this->b = createCommunity();
});

describe('the strict scope', function () {
    it('shows only the current community\'s rows', function () {
        CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));
        CommunityContext::run($this->b, fn () => Note::create(['name' => 'b']));
        Note::withoutCommunityGuard(fn () => Note::create(['name' => 'orphan']));

        CommunityContext::run($this->a, function () {
            expect(Note::pluck('name')->all())->toBe(['a'])
                ->and(Note::count())->toBe(1);
        });

        CommunityContext::run($this->b, fn () => expect(Note::pluck('name')->all())->toBe(['b']));
    });

    it('shows nothing with no current community (fail closed)', function () {
        CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        expect(Note::count())->toBe(0)
            ->and(Note::withoutCommunityScope()->count())->toBe(1);
    });

    it('can show every row with no community, by config', function () {
        config()->set('community-manager.isolation.when_no_community', 'all');
        CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));
        CommunityContext::run($this->b, fn () => Note::create(['name' => 'b']));

        expect(Note::count())->toBe(2);
        CommunityContext::run($this->a, fn () => expect(Note::count())->toBe(1));
    });

    it('reaches other communities only on purpose', function () {
        CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));
        CommunityContext::run($this->b, fn () => Note::create(['name' => 'b']));

        CommunityContext::run($this->b, function () {
            expect(Note::forCommunity($this->a)->pluck('name')->all())->toBe(['a'])
                ->and(Note::forCommunity([$this->a->id, $this->b->id])->count())->toBe(2)
                ->and(Note::withoutCommunityScope()->count())->toBe(2);
        });
    });

    it('scopes mass updates and deletes', function () {
        CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));
        CommunityContext::run($this->b, fn () => Note::create(['name' => 'b']));

        CommunityContext::run($this->b, function () {
            Note::query()->update(['name' => 'changed']);
            Note::query()->delete();
        });

        expect(Note::withoutCommunityScope()->pluck('name')->all())->toBe(['a']);
    });

    it('shares rows with no community when the model opts in', function () {
        SharedTag::withoutCommunityGuard(fn () => SharedTag::create(['name' => 'system']));
        CommunityContext::run($this->a, fn () => SharedTag::create(['name' => 'a']));
        CommunityContext::run($this->b, fn () => SharedTag::create(['name' => 'b']));

        CommunityContext::run($this->a, fn () => expect(SharedTag::orderBy('name')->pluck('name')->all())->toBe(['a', 'system']));

        // With no community, only the shared rows.
        expect(SharedTag::pluck('name')->all())->toBe(['system']);
    });

    it('relates the row to its community', function () {
        $note = CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        expect($note->community->is($this->a))->toBeTrue()
            ->and(CommunityContext::run($this->a, fn () => $note->belongsToCurrentCommunity()))->toBeTrue()
            ->and(CommunityContext::run($this->b, fn () => $note->belongsToCurrentCommunity()))->toBeFalse();
    });
});

describe('creating', function () {
    it('fills the community from the context', function () {
        $note = CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        expect($note->community_id)->toBe($this->a->id);
    });

    it('refuses a row with no community and none current', function () {
        expect(fn () => Note::create(['name' => 'x']))->toThrow(MissingCommunityException::class);
    });

    it('accepts an explicit community with none current (seeders, central code)', function () {
        $note = Note::create(['name' => 'x', 'community_id' => $this->a->id]);

        expect($note->community_id)->toBe($this->a->id);
    });

    it('allows rows with no community for a model that shares them', function () {
        $tag = SharedTag::create(['name' => 'system']);

        expect($tag->community_id)->toBeNull();
    });

    it('can allow rows with no community by config', function () {
        config()->set('community-manager.isolation.require_community_on_create', false);

        expect(Note::create(['name' => 'x'])->community_id)->toBeNull();
    });
});

describe('the write guard', function () {
    it('refuses creating a row in another community', function () {
        CommunityContext::run($this->b, function () {
            expect(fn () => Note::create(['name' => 'x', 'community_id' => $this->a->id]))->toThrow(CommunityMismatchException::class);
        });

        expect(Note::withoutCommunityScope()->count())->toBe(0);
    });

    it('refuses saving, deleting or moving another community\'s row', function () {
        $note = CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        CommunityContext::run($this->b, function () use ($note) {
            expect(fn () => $note->update(['name' => 'changed']))->toThrow(CommunityMismatchException::class)
                ->and(fn () => $note->delete())->toThrow(CommunityMismatchException::class);
        });

        CommunityContext::run($this->a, function () use ($note) {
            $note->refresh();
            $note->community_id = $this->b->id;
            expect(fn () => $note->save())->toThrow(CommunityMismatchException::class);
        });

        expect(Note::withoutCommunityScope()->find($note->id))
            ->name->toBe('a')
            ->community_id->toBe($this->a->id);
    });

    it('allows deliberate cross-community writes', function () {
        $note = CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        CommunityContext::run($this->b, function () use ($note) {
            Note::withoutCommunityGuard(fn () => $note->update(['community_id' => $this->b->id]));
        });

        expect($note->fresh()->community_id)->toBe($this->b->id);
    });

    it('doesn\'t guard writes with no current community', function () {
        $note = Note::create(['name' => 'a', 'community_id' => $this->a->id]);
        $note->update(['community_id' => $this->b->id]);

        expect(Note::withoutCommunityScope()->find($note->id)->community_id)->toBe($this->b->id);
    });

    it('refuses editing shared rows from inside a community', function () {
        $tag = SharedTag::create(['name' => 'system']);

        CommunityContext::run($this->a, function () use ($tag) {
            expect(fn () => $tag->update(['name' => 'mine']))->toThrow(CommunityMismatchException::class);
        });
    });

    it('can be turned off by config', function () {
        config()->set('community-manager.isolation.guard_writes', false);
        $note = CommunityContext::run($this->a, fn () => Note::create(['name' => 'a']));

        CommunityContext::run($this->b, fn () => $note->update(['name' => 'changed']));

        expect(Note::withoutCommunityScope()->find($note->id)->name)->toBe('changed');
    });
});

describe('CommunityIsolation test helpers', function () {
    it('lists models with a community column that lack the trait', function () {
        $directory = __DIR__.'/../../Fixtures/Models';
        $namespace = 'jfsullivan\\CommunityManager\\Tests\\Fixtures\\Models';

        expect(CommunityIsolation::unscopedModels($directory, $namespace))->toBe([UnscopedEntry::class])
            ->and(CommunityIsolation::unscopedModels($directory, $namespace, except: [UnscopedEntry::class]))->toBe([]);

        CommunityIsolation::assertModelsAreScoped($directory, $namespace, except: [UnscopedEntry::class]);

        expect(fn () => CommunityIsolation::assertModelsAreScoped($directory, $namespace))
            ->toThrow(AssertionFailedError::class, UnscopedEntry::class);
    });

    it('proves a scoped model is isolated', function () {
        CommunityIsolation::assertIsolated(Note::class, fn () => Note::create(['name' => 'row']));
    });

    it('catches a model that isn\'t isolated', function () {
        expect(fn () => CommunityIsolation::assertIsolated(
            UnscopedEntry::class,
            fn ($community) => UnscopedEntry::create(['name' => 'row', 'community_id' => $community->id]),
        ))->toThrow(AssertionFailedError::class, 'leaks into another community');
    });
});
