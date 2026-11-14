<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use jfsullivan\CommunityManager\Context\Tasks\RegisterCommunityViewPaths;
use jfsullivan\CommunityManager\Contracts\ProvidesCommunityViewPaths;
use jfsullivan\CommunityManager\Facades\CommunityContext;

class TemplateFolders implements ProvidesCommunityViewPaths
{
    public static string $root;

    public function viewPaths(Model $community): array
    {
        return [static::$root.'/'.$community->slug];
    }

    public function componentPaths(Model $community): array
    {
        return [static::$root.'/'.$community->slug.'/components' => null];
    }
}

beforeEach(function () {
    TemplateFolders::$root = sys_get_temp_dir().'/cm-views-'.uniqid();

    foreach (['acme' => 'Acme', 'bay' => 'Bay'] as $slug => $label) {
        File::ensureDirectoryExists(TemplateFolders::$root."/{$slug}/components");
        File::put(TemplateFolders::$root."/{$slug}/greeting.blade.php", "Hello from {$label}");
        File::put(TemplateFolders::$root."/{$slug}/components/badge.blade.php", "{$label} badge");
    }

    File::ensureDirectoryExists(TemplateFolders::$root.'/base');
    File::put(TemplateFolders::$root.'/base/greeting.blade.php', 'Hello from the app');
    app('view')->getFinder()->prependLocation(TemplateFolders::$root.'/base');

    config()->set('view.compiled', TemplateFolders::$root.'/compiled');
    File::ensureDirectoryExists(TemplateFolders::$root.'/compiled');

    config()->set('community-manager.context.view_paths', TemplateFolders::class);
    config()->set('community-manager.context.tasks', [RegisterCommunityViewPaths::class]);

    $this->acme = createCommunity();
    $this->acme->forceFill(['slug' => 'acme'])->save();
    $this->bay = createCommunity();
    $this->bay->forceFill(['slug' => 'bay'])->save();
});

afterEach(function () {
    File::deleteDirectory(TemplateFolders::$root);
});

it('puts each community\'s views first and restores the app\'s after', function () {
    $pathsBefore = app('view')->getFinder()->getPaths();
    $componentsBefore = Blade::getAnonymousComponentPaths();

    CommunityContext::run($this->acme, fn () => expect(view('greeting')->render())->toBe('Hello from Acme'));
    CommunityContext::run($this->bay, fn () => expect(view('greeting')->render())->toBe('Hello from Bay'));

    expect(view('greeting')->render())->toBe('Hello from the app')
        ->and(app('view')->getFinder()->getPaths())->toBe($pathsBefore)
        ->and(Blade::getAnonymousComponentPaths())->toBe($componentsBefore);
});

it('never lets one community\'s components answer for the next', function () {
    CommunityContext::set($this->acme);
    expect(Blade::render('<x-badge />'))->toBe('Acme badge');

    CommunityContext::set($this->bay);
    expect(Blade::render('<x-badge />'))->toBe('Bay badge')
        ->and(collect(Blade::getAnonymousComponentPaths())->pluck('path')->filter(fn ($path) => str_contains($path, 'acme'))->all())->toBe([]);
});
