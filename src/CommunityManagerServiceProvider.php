<?php

namespace jfsullivan\CommunityManager;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Compilers\BladeCompiler;
use jfsullivan\ArticleManager\Policies\ArticlePolicy;
use jfsullivan\CommunityManager\Commands\CommunityManagerCommand;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityIsEnabled;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityIsWritable;
use jfsullivan\CommunityManager\Http\Middleware\EnsureCommunityTracksMemberAccounting;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityAdmin;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityMember;
use jfsullivan\CommunityManager\Http\Middleware\EnsureIsCommunityOwner;
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
            ->hasRoute('web')
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
            ->hasCommand(CommunityManagerCommand::class);
    }

    public function packageBooted()
    {
        config()->set('member-manager.user_model', config('community-manager.user_model'));

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('community-owner', EnsureIsCommunityOwner::class);
        $router->aliasMiddleware('community-admin', EnsureIsCommunityAdmin::class);
        $router->aliasMiddleware('community-member', EnsureIsCommunityMember::class);
        $router->aliasMiddleware('community-accounting', EnsureCommunityTracksMemberAccounting::class);

        // Re-checked on Livewire updates: component actions on the accounting
        // pages don't pass through the route middleware again.
        Livewire::addPersistentMiddleware([EnsureCommunityTracksMemberAccounting::class]);

        $this->configureDisabling();

        // Opt-in (membership_status.enabled): the page a pending or
        // turned-down request is sent to. Off by default, so an app with its
        // own copy of the route isn't affected until it adopts it.
        if (config('community-manager.membership_status.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/membership-status.php');
        }

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

        ArticlePolicy::registerGates('community');
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
