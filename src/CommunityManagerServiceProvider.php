<?php

namespace jfsullivan\CommunityManager;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Compilers\BladeCompiler;
use jfsullivan\ArticleManager\Policies\ArticlePolicy;
use jfsullivan\CommunityManager\Commands\CommunityManagerCommand;
use jfsullivan\CommunityManager\Commands\RunInCommunityCommand;
use jfsullivan\CommunityManager\Context\CommunityContext;
use jfsullivan\CommunityManager\Context\Resolvers\HostResolver;
use jfsullivan\CommunityManager\Contracts\ChecksMembership;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityContext;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityIsEnabled;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityIsWritable;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityTracksMemberAccounting;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityAdmin;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityMember;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityOwner;
use jfsullivan\CommunityManager\Http\Middleware\SetCommunityContext;
use jfsullivan\CommunityManager\Livewire\Accounting\Components\MemberBalance;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\AddFundsModal;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\CreateTransactionModal;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\DeleteTransactionModal;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\UpdateTransactionModal;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\CommunityTransactionsPage;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberBalancesPage;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberTransactionsPage;
use jfsullivan\CommunityManager\Livewire\CommunityMenu;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Livewire\Header;
use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberManagementPage;
use jfsullivan\CommunityManager\Livewire\Modals\JoinCommunity;
use jfsullivan\CommunityManager\Livewire\Pages\CreateCommunityPage;
use jfsullivan\CommunityManager\Livewire\ResponsiveNavigationMenu;
use jfsullivan\CommunityManager\Membership\MembershipsChecker;
use jfsullivan\CommunityManager\Membership\ScopedUsersChecker;
use jfsullivan\CommunityManager\Queue\PropagatesCommunityContext;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CommunityManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('community-manager')
            ->hasConfigFile('community-manager')
            ->hasTranslations()
            ->hasViews()
            ->hasMigrations([
                'create_communities_table',
                'add_current_community_column_to_users_table',
                'member_manager_create_member_roles_table',
                'member_manager_create_membership_types_table',
                'member_manager_create_memberships_table',
                'member_manager_create_mail_templates_table',
                'member_manager_create_invitations_table',
                'add_member_accounting_settings_to_communities_table',
                'add_joining_settings_to_communities_table',
                'add_disabled_columns_to_communities_table',
            ])
            ->hasCommands([CommunityManagerCommand::class, RunInCommunityCommand::class]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(CommunityContext::class);

        $this->app->bind(ChecksMembership::class, function ($app) {
            $checker = config('community-manager.membership.checker')
                ?? (config('community-manager.membership.mode', 'memberships') === 'scoped_users'
                    ? ScopedUsersChecker::class
                    : MembershipsChecker::class);

            return $app->make($checker);
        });
    }

    public function packageBooted()
    {
        config()->set('member-manager.user_model', config('community-manager.user_model'));

        // The community pages (/current-community, /communities/create and
        // community.*). An app that only uses the community core turns them off.
        if (config('community-manager.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('community-owner', EnsureIsCommunityOwner::class);
        $router->aliasMiddleware('community-admin', EnsureIsCommunityAdmin::class);
        $router->aliasMiddleware('community-member', EnsureIsCommunityMember::class);
        $router->aliasMiddleware('community-accounting', EnsureCommunityTracksMemberAccounting::class);
        $router->aliasMiddleware('community.context', SetCommunityContext::class);
        $router->aliasMiddleware('community.required', EnsureCommunityContext::class);

        // Re-checked on Livewire updates: component actions on the accounting
        // pages don't pass through the route middleware again.
        Livewire::addPersistentMiddleware([
            EnsureCommunityTracksMemberAccounting::class,
            SetCommunityContext::class,
            EnsureCommunityContext::class,
        ]);

        $this->configureDisabling();
        $this->configureContext();
        $this->publishOptionalMigrations();

        Relation::morphMap([
            'community' => config('community-manager.community_model'),
            'user' => config('community-manager.user_model'),
            'transaction' => config('community-manager.transaction_model'),
        ]);

        $this->configurePolicies();
        $this->configureComponents();
        $this->configureLivewireComponents();
    }

    /**
     * The community context: auth changes forget a lazily resolved
     * community, queued jobs run inside the community they were dispatched
     * in, and (when HostResolver is in use) a changed custom domain drops the
     * cached domain map.
     */
    protected function configureContext(): void
    {
        $events = $this->app->make(Dispatcher::class);

        $events->listen([Login::class, Logout::class, Authenticated::class], function () {
            $this->app->make(CommunityContext::class)->refresh();
        });

        if (config('community-manager.queue.propagate', true)) {
            $this->app->make(PropagatesCommunityContext::class)->register($events);
        }

        $communityClass = config('community-manager.community_model');

        if (! $communityClass || ! class_exists($communityClass)) {
            return;
        }

        // Read at event time, so config changed after boot still applies.
        $usesHosts = fn () => config('community-manager.hosts.custom_domain_column', 'custom_domain')
            && in_array(HostResolver::class, (array) config('community-manager.context.resolvers', []), true);

        $communityClass::saved(function ($community) use ($usesHosts) {
            if (! $usesHosts()) {
                return;
            }

            $column = config('community-manager.hosts.custom_domain_column', 'custom_domain');

            if (($community->wasRecentlyCreated && $community->getAttribute($column) !== null) || $community->wasChanged($column)) {
                HostResolver::forgetCustomDomains();
            }
        });

        $communityClass::deleted(function () use ($usesHosts) {
            if ($usesHosts()) {
                HostResolver::forgetCustomDomains();
            }
        });
    }

    /**
     * Opt-in migrations, published on request only:
     * community-manager-site-migrations (slug and custom_domain for
     * HostResolver) and community-manager-scoped-users-migrations
     * (users.community_id for membership mode 'scoped_users').
     */
    protected function publishOptionalMigrations(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $now = now();

        $this->publishes([
            __DIR__.'/../database/migrations/add_site_columns_to_communities_table.php.stub' => database_path('migrations/'.$now->format('Y_m_d_His').'_add_site_columns_to_communities_table.php'),
        ], 'community-manager-site-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/add_community_id_to_users_table.php.stub' => database_path('migrations/'.$now->addSecond()->format('Y_m_d_His').'_add_community_id_to_users_table.php'),
        ], 'community-manager-scoped-users-migrations');
    }

    /**
     * Opt-in (community-manager.disabling.enabled): the disabled / unavailable
     * pages and the web-group middleware that lock a disabled community and
     * keep it read-only. Off by default, so an app with its own copies of
     * these routes isn't affected until it adopts them.
     *
     * The middleware goes through the HTTP kernel, not the router: the
     * kernel re-syncs its groups to the router when it is built, which can be
     * after boot (tests, artisan, queue), wiping router-only additions. It is
     * appended to the end of the web group; an app that needs it earlier
     * (e.g. before its own billing redirect) turns off
     * `disabling.register_middleware` and adds both classes itself.
     */
    protected function configureDisabling(): void
    {
        if (! config('community-manager.disabling.enabled', false)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/disabling.php');

        Livewire::addPersistentMiddleware([EnsureCommunityIsEnabled::class, EnsureCommunityIsWritable::class]);

        if (! config('community-manager.disabling.register_middleware', true)) {
            return;
        }

        $kernel = $this->app->make(HttpKernel::class);
        $kernel->appendMiddlewareToGroup('web', EnsureCommunityIsEnabled::class);
        $kernel->appendMiddlewareToGroup('web', EnsureCommunityIsWritable::class);
    }

    protected function configurePolicies()
    {
        $communityClass = config('community-manager.community_model');
        $communityPolicyClass = config('community-manager.community_policy');

        Gate::policy($communityClass, $communityPolicyClass);

        $transactionPolicyClass = app(config('community-manager.transaction_policy'));

        Gate::define('create-community-transaction', [$transactionPolicyClass, 'create']);
        Gate::define('edit-community-transaction', [$transactionPolicyClass, 'update']);
        Gate::define('update-community-transaction', [$transactionPolicyClass, 'update']);
        Gate::define('delete-community-transaction', [$transactionPolicyClass, 'delete']);

        // article-manager is optional: without it the article gates and pages are left out.
        if (class_exists(ArticlePolicy::class)) {
            ArticlePolicy::registerGates('community');
        }
    }

    protected function configureComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Blade::componentNamespace('jfsullivan\\CommunityManager\\View\\Components', 'community-manager');

            // Global <x-money> component (replaces the retired ui-kit one used by the accounting views).
            Blade::anonymousComponentPath(__DIR__.'/../resources/views/global-components');

            $this->registerComponent('layouts.admin.sidebar.index', 'layouts.admin.sidebar');
            $this->registerComponent('layouts.admin.sidebar.menu', 'layouts.admin.sidebar.menu');

            $this->registerComponent('selectable-community');
            $this->registerComponent('switchable-community');

            $this->registerComponent('profile-menu');
            $this->registerComponent('profile-menu.link');
            $this->registerComponent('responsive-navigation-menu.section');
        });
    }

    protected function configureLivewireComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Livewire::component('community-manager.header', Header::class);
            Livewire::component('community-manager.dashboard', Dashboard::class);
            Livewire::component('community-manager.community-menu', CommunityMenu::class);
            Livewire::component('community-manager.responsive-navigation-menu', ResponsiveNavigationMenu::class);
            Livewire::component('community-manager.modals.join-community', JoinCommunity::class);
            Livewire::component('community-manager.pages.create-community-page', CreateCommunityPage::class);

            Livewire::component('community-manager.memberships.pages.member-management-page', MemberManagementPage::class);

            Livewire::component('community-manager.accounting.components.member-balance', MemberBalance::class);
            Livewire::component('community-manager.accounting.modals.add-funds-modal', AddFundsModal::class);
            Livewire::component('community-manager.accounting.modals.create-transaction-modal', CreateTransactionModal::class);
            Livewire::component('community-manager.accounting.modals.delete-transaction-modal', DeleteTransactionModal::class);
            Livewire::component('community-manager.accounting.modals.update-transaction-modal', UpdateTransactionModal::class);
            Livewire::component('community-manager.accounting.pages.community-transactions-page', CommunityTransactionsPage::class);
            Livewire::component('community-manager.accounting.pages.member-balances-page', MemberBalancesPage::class);
            Livewire::component('community-manager.accounting.pages.member-transactions-page', MemberTransactionsPage::class);
        });
    }

    protected function registerComponent(string $component, ?string $name = null, $class = null)
    {
        if (! empty($class)) {
            Blade::component('community-manager::'.$component, $class);
        } else {
            Blade::component('community-manager::components.'.$component, 'community-manager::'.($name ?? $component));
        }
    }
}
