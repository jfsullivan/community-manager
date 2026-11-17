<?php

namespace jfsullivan\CommunityManager\Testing;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Exceptions\CommunityMismatchException;
use jfsullivan\CommunityManager\Isolation\CommunityScope;
use jfsullivan\CommunityManager\Isolation\IsolationOptions;
use jfsullivan\CommunityManager\Traits\BelongsToCommunity;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Drop-in isolation checks for an app's test suite (Pest or PHPUnit):
 *
 *     it('scopes every community model', function () {
 *         CommunityIsolation::assertModelsAreScoped(app_path('Models'), 'App\\Models', except: [Community::class]);
 *     });
 *
 *     it('isolates pools', function () {
 *         CommunityIsolation::assertIsolated(Pool::class, fn (Community $community) => Pool::factory()->create());
 *     });
 */
class CommunityIsolation
{
    /**
     * Concrete Eloquent models under the directory whose table has the
     * community column but which don't use BelongsToCommunity.
     *
     * @param  array<int, class-string>  $except
     * @return array<int, class-string<Model>>
     */
    public static function unscopedModels(string $directory, string $namespace, array $except = [], ?string $column = null): array
    {
        $column ??= config('community-manager.isolation.column', 'community_id');
        $unscoped = [];

        foreach (static::modelsIn($directory, $namespace) as $class) {
            if (in_array($class, $except, true)) {
                continue;
            }

            $model = new $class;

            if (! Schema::connection($model->getConnectionName())->hasColumn($model->getTable(), $column)) {
                continue;
            }

            if (! in_array(BelongsToCommunity::class, class_uses_recursive($class), true)) {
                $unscoped[] = $class;
            }
        }

        sort($unscoped);

        return $unscoped;
    }

    /**
     * Fails listing every model with a community column that doesn't use
     * BelongsToCommunity. Run it after migrations so table columns are known.
     *
     * @param  array<int, class-string>  $except
     */
    public static function assertModelsAreScoped(string $directory, string $namespace, array $except = [], ?string $column = null): void
    {
        $unscoped = static::unscopedModels($directory, $namespace, $except, $column);

        Assert::assertSame([], $unscoped, "These models have a community column but don't use BelongsToCommunity (add the trait, or list them in \$except):\n- ".implode("\n- ", $unscoped));
    }

    /**
     * Proves a BelongsToCommunity model is isolated: a row made in one
     * community can't be found, counted, updated or deleted from another,
     * a new row can't be pointed at the other community, and forCommunity()
     * still reaches it deliberately.
     *
     * @param  class-string<Model>  $modelClass
     * @param  Closure(Model $community): Model  $make  Creates one row; called inside the community's context.
     * @param  (Closure(): Model)|null  $makeCommunity  Creates a community (default: the community model's factory).
     */
    public static function assertIsolated(string $modelClass, Closure $make, ?Closure $makeCommunity = null): void
    {
        Assert::assertContains(BelongsToCommunity::class, class_uses_recursive($modelClass), "{$modelClass} leaks into another community: it doesn't use BelongsToCommunity.");

        $context = app(CommunityContext::class);
        $makeCommunity ??= function () use ($context) {
            $communityClass = $context->communityModel();

            if (! method_exists($communityClass, 'factory')) {
                Assert::fail('Pass $makeCommunity: the community model has no factory.');
            }

            return $communityClass::factory()->create();
        };

        $home = $makeCommunity();
        $other = $makeCommunity();

        $row = $context->run($home, fn () => $make($home));
        $column = IsolationOptions::column($row);
        $key = $row->getKey();

        Assert::assertSame((string) $home->getKey(), (string) $row->getAttribute($column), "{$modelClass} wasn't created in the current community.");

        $context->run($home, function () use ($modelClass, $key) {
            Assert::assertNotNull($modelClass::query()->find($key), "{$modelClass} isn't visible in its own community.");
        });

        $context->run($other, function () use ($modelClass, $key, $other, $column) {
            Assert::assertNull($modelClass::query()->find($key), "{$modelClass} leaks into another community (find).");
            Assert::assertFalse($modelClass::query()->whereKey($key)->exists(), "{$modelClass} leaks into another community (exists).");
            Assert::assertSame(0, $modelClass::query()->whereKey($key)->count(), "{$modelClass} leaks into another community (count).");
            Assert::assertSame(0, $modelClass::query()->whereKey($key)->update([$column => $other->getKey()]), "{$modelClass} can be mass-updated from another community.");

            $foreign = $modelClass::query()->withoutGlobalScope(CommunityScope::class)->find($key);
            Assert::assertNotNull($foreign, "{$modelClass}::withoutCommunityScope() can't reach the row.");

            static::assertRefused(fn () => $foreign->touch(), "{$modelClass} can be saved from another community.");
            static::assertRefused(fn () => $foreign->delete(), "{$modelClass} can be deleted from another community.");

            $moved = $modelClass::query()->withoutGlobalScope(CommunityScope::class)->find($key);
            $moved->setAttribute($column, $other->getKey());
            static::assertRefused(fn () => $moved->save(), "{$modelClass} can be moved into another community.");
        });

        $context->run($home, function () use ($modelClass, $key, $column, $other) {
            $row = $modelClass::query()->find($key);
            $row->setAttribute($column, $other->getKey());
            static::assertRefused(fn () => $row->save(), "{$modelClass} can be pointed at another community.");
        });
    }

    protected static function assertRefused(Closure $write, string $message): void
    {
        try {
            $write();
        } catch (CommunityMismatchException) {
            return;
        } catch (Throwable $exception) {
            Assert::fail($message.' (threw '.$exception::class.': '.$exception->getMessage().')');
        }

        Assert::fail($message);
    }

    /** @return array<int, class-string<Model>> */
    protected static function modelsIn(string $directory, string $namespace): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $models = [];

        foreach (Finder::create()->files()->in($directory)->name('*.php') as $file) {
            $relative = Str::of($file->getRelativePathname())->replaceLast('.php', '')->replace(['/', '\\'], '\\');
            $class = rtrim($namespace, '\\').'\\'.$relative;

            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            if ((new ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $models[] = $class;
        }

        return $models;
    }
}
