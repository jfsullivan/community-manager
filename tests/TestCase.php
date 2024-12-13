<?php

namespace jfsullivan\CommunityManager\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'jfsullivan\\CommunityManager\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \jfsullivan\CommunityManager\CommunityManagerServiceProvider::class,
            \jfsullivan\MemberManager\MemberManagerServiceProvider::class,
            \jfsullivan\UiKit\UiKitServiceProvider::class,
            \jfsullivan\UiKitIcons\UiKitIconsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('view.paths', [__DIR__.'/resources/views', __DIR__.'/tests/resources/views']);

        Blade::component('layouts.app', AppLayout::class);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/create_communities_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/add_current_community_column_to_users_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_member_roles_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_membership_status_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_memberships_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_mail_templates_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_invitations_table.php.stub';
        $migration->up();

        $app['db']->connection()->table('member_roles')->insert([
            'name' => 'Member',
            'slug' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $app['db']->connection()->table('membership_status')->insert([
            'name' => 'Active',
            'slug' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
