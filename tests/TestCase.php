<?php

namespace jfsullivan\CommunityManager\Tests;

use BladeUI\Icons\BladeIconsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use jfsullivan\CommonHelpers\CommonHelpersServiceProvider;
use jfsullivan\CommunityManager\CommunityManagerServiceProvider;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Tests\Components\AppLayout;
use jfsullivan\MemberManager\MemberManagerServiceProvider;
use jfsullivan\MemberManager\Models\Membership;
use jfsullivan\MemberManager\Models\Role;
use jfsullivan\UiKit\UiKitServiceProvider;
use jfsullivan\UiKitIcons\UiKitIconsServiceProvider;
use jfsullivan\UserTimezone\UserTimezoneServiceProvider;
use Livewire\LivewireServiceProvider;
use LivewireUI\Modal\LivewireModalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelOptions\OptionsServiceProvider;

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
            BladeIconsServiceProvider::class,
            CommonHelpersServiceProvider::class,
            CommunityManagerServiceProvider::class,
            MemberManagerServiceProvider::class,
            UiKitServiceProvider::class,
            UiKitIconsServiceProvider::class,
            LivewireServiceProvider::class,
            LivewireModalServiceProvider::class,
            UserTimezoneServiceProvider::class,
            OptionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('view.paths', [__DIR__.'/resources/views', __DIR__.'/tests/resources/views']);
        config()->set('community-manager.user_model', User::class);
        config()->set('community-manager.community_model', Community::class);
        config()->set('community-manager.transaction_model', Transaction::class);
        config()->set('community-manager.admin_layout', 'layouts.app');
        config()->set('member-manager.name_type', 'full_name');
        config()->set('member-manager.membership_model', Membership::class);
        config()->set('member-manager.role_model', Role::class);

        Blade::component('layouts.app', AppLayout::class);

        // Set up authorization gates for dashboard view
        Gate::define('view-member-balance', function ($user, $community) {
            return true; // Allow all for testing
        });

        Gate::define('add-funds', function ($user, $community) {
            return true; // Allow all for testing
        });

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('profile_photo_path')->nullable();
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
