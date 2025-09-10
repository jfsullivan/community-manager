<?php

namespace jfsullivan\CommunityManager\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use jfsullivan\CommunityManager\Tests\Components\AppLayout;
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
            \LivewireUI\Modal\LivewireModalServiceProvider::class,
            \JamesMills\LaravelTimezone\LaravelTimezoneServiceProvider::class,
            \Spatie\LaravelOptions\OptionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('view.paths', [__DIR__.'/resources/views', __DIR__.'/tests/resources/views']);
        config()->set('community-manager.user_model', \jfsullivan\CommunityManager\Tests\User::class);
        config()->set('community-manager.community_model', \jfsullivan\CommunityManager\Models\Community::class);
        config()->set('community-manager.transaction_model', \jfsullivan\CommunityManager\Models\Transaction::class);
        config()->set('member-manager.name_type', 'full_name');

        Blade::component('layouts.app', AppLayout::class);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->unsignedBigInteger('current_community_id')->nullable();
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

        $migration = include __DIR__.'/../database/migrations/member_manager_create_membership_types_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_memberships_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_mail_templates_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/member_manager_create_invitations_table.php.stub';
        $migration->up();

        // Transaction-related migrations
        $migration = include __DIR__.'/../database/migrations/create_transaction_types_table.php.stub';
        $migration->up();
        
        $migration = include __DIR__.'/../database/migrations/create_transactions_table.php.stub';
        $migration->up();

        $app['db']->connection()->table('member_roles')->insert([
            'name' => 'Member',
            'slug' => 'member',
            'color' => 'blue',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $app['db']->connection()->table('membership_types')->insert([
            'name' => 'Active',
            'slug' => 'active',
            'color' => 'green',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
