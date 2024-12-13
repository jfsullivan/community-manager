<?php

namespace jfsullivan\CommunityManager;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\Compilers\BladeCompiler;
use jfsullivan\CommunityManager\Commands\CommunityManagerCommand;
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
use jfsullivan\CommunityManager\Livewire\Articles\CommunityArticlesIndexPage;
use jfsullivan\CommunityManager\Livewire\CommunityMenu;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Livewire\Header;
use jfsullivan\CommunityManager\Livewire\Memberships\Modals\AddMemberModal;
use jfsullivan\CommunityManager\Livewire\Memberships\Modals\ImportMembersModal;
use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberManagementPage;
use jfsullivan\CommunityManager\Livewire\Modals\JoinCommunity;
use jfsullivan\CommunityManager\Livewire\NavigationMenu;
use jfsullivan\CommunityManager\Livewire\Pages\CreateCommunityPage;
use jfsullivan\CommunityManager\Livewire\ResponsiveNavigationMenu;
use jfsullivan\CommunityManager\Mixins\CustomMoney;
use jfsullivan\CommunityManager\Policies\CommunityArticlePolicy;
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
            ])
            ->hasCommand(CommunityManagerCommand::class);
    }

    public function packageRegistered()
    {
        // Money::mixin(new CustomMoney());
    }

    public function packageBooted()
    {
        config()->set('member-manager.user_model', config('community-manager.user_model'));

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('community-owner', EnsureIsCommunityOwner::class);
        $router->aliasMiddleware('community-admin', EnsureIsCommunityAdmin::class);
        $router->aliasMiddleware('community-member', EnsureIsCommunityMember::class);

        Relation::morphMap([
            'community' => config('community-manager.community_model'),
            'user' => config('community-manager.user_model'),
            'transaction' => config('community-manager.transaction_model'),
        ]);

        $this->configurePolicies();
        $this->configureComponents();
        $this->configureLivewireComponents();
    }

    protected function configurePolicies()
    {
        $communityClass = config('community-manager.community_model');
        $communityPolicyClass = config('community-manager.community_policy');

        Gate::policy($communityClass, $communityPolicyClass);

        // Gate::define('manage-site', function ($user) {
        //     return $user->isSiteAdmin();
        // });

        // $communityPolicyClass = app(config('community-manager.transaction_policy'));

        $transactionPolicyClass = app(config('community-manager.transaction_policy'));

        Gate::define('create-community-transaction', [$transactionPolicyClass, 'create']);
        Gate::define('edit-community-transaction', [$transactionPolicyClass, 'update']);
        Gate::define('update-community-transaction', [$transactionPolicyClass, 'update']);
        Gate::define('delete-community-transaction', [$transactionPolicyClass, 'delete']);

        Gate::define('view-community-article', [CommunityArticlePolicy::class, 'view']);
        Gate::define('create-community-article', [CommunityArticlePolicy::class, 'create']);
        Gate::define('edit-community-article', [CommunityArticlePolicy::class, 'edit']);
        Gate::define('update-community-article', [CommunityArticlePolicy::class, 'update']);
        Gate::define('delete-community-article', [CommunityArticlePolicy::class, 'delete']);
        Gate::define('publish-community-article', [CommunityArticlePolicy::class, 'publish']);
        Gate::define('unpublish-community-article', [CommunityArticlePolicy::class, 'unpublish']);
        Gate::define('send-community-article', [CommunityArticlePolicy::class, 'send']);
    }

    protected function configureComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Blade::componentNamespace('jfsullivan\\CommunityManager\\View\\Components', 'community-manager');

            // $this->registerComponent('layouts.admin.index', 'layouts.admin');
            $this->registerComponent('layouts.admin.sidebar.index', 'layouts.admin.sidebar');
            $this->registerComponent('layouts.admin.sidebar.menu', 'layouts.admin.sidebar.menu');
            // $this->registerComponent('layouts.admin.side-bar-item');
            // $this->registerComponent('layouts.admin.side-bar-section-header');
            // $this->registerComponent('layouts.admin.slide-over-menu');

            // $this->registerComponent('application-logo');
            // $this->registerComponent('application-mark');

            $this->registerComponent('selectable-community');
            $this->registerComponent('switchable-community');

            $this->registerComponent('profile-menu');
            $this->registerComponent('profile-menu.link');
            $this->registerComponent('responsive-navigation-menu.section');

            // Accounting
            // $this->registerComponent('accounting.transactions.transaction-detail');

            // $this->registerComponent('profile-menu.link');
        });
    }

    protected function configureLivewireComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            // Livewire::component('community-manager::livewire.mail-preview', MailPreview::class);

            // Livewire::component('community-manager::livewire.admin.site-admin-dashboard', SiteAdminDashboard::class);

            Livewire::component('community-manager::header', Header::class);
            Livewire::component('community-manager::dashboard', Dashboard::class);
            Livewire::component('community-manager::community-menu', CommunityMenu::class);
            Livewire::component('community-manager::responsive-navigation-menu', ResponsiveNavigationMenu::class);
            // Livewire::component('community.navigation-menu', NavigationMenu::class);
            Livewire::component('community-manager::modals.join-community', JoinCommunity::class);
            Livewire::component('community-manager::pages.create-community-page', CreateCommunityPage::class);

            Livewire::component('community-manager::articles.community-articles-index-page', CommunityArticlesIndexPage::class);

            Livewire::component('community-manager::memberships.pages.member-management-page', MemberManagementPage::class);
            Livewire::component('community-manager::memberships.modals.add-member-modal', AddMemberModal::class);
            Livewire::component('community-manager::memberships.modals.import-members-modal', ImportMembersModal::class);

            Livewire::component('community-manager::accounting.components.member-balance', MemberBalance::class);
            Livewire::component('community-manager::accounting.modals.add-funds-modal', AddFundsModal::class);
            Livewire::component('community-manager::accounting.modals.create-transaction-modal', CreateTransactionModal::class);
            Livewire::component('community-manager::accounting.modals.delete-transaction-modal', DeleteTransactionModal::class);
            Livewire::component('community-manager::accounting.modals.update-transaction-modal', UpdateTransactionModal::class);
            Livewire::component('community-manager::accounting.pages.community-transactions-page', CommunityTransactionsPage::class);
            Livewire::component('community-manager::accounting.pages.member-balances-page', MemberBalancesPage::class);
            Livewire::component('community-manager::accounting.pages.member-transactions-page', MemberTransactionsPage::class);

            // Livewire::component('community-manager::livewire.accounting.member-transactions-page', MemberTransactionsPage::class);

            // Livewire::component('community-manager::community.create-community-form', CreateOrganizationForm::class);
            // Livewire::component('community-manager::community.update-community-name-form', UpdateOrganizationNameForm::class);
            // Livewire::component('community-manager::community.delete-community-form', DeleteOrganizationForm::class);

            // Livewire::component('community-manager::livewire.communities.admin.configuration.settings', Settings::class);
            // Livewire::component('community-manager::livewire.communities.admin.configuration.invitations', Invitations::class);
        });
    }

    protected function registerComponent(string $component, ?string $name = null, $class = null)
    {
        if (! empty($class)) {
            Blade::component('community-manager::'.$component, $class);
        } else {
            Blade::component('community-manager::components.'.$component, 'community-manager::'.$name ?? $component);
        }
    }
}
