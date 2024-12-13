<?php

use Illuminate\Support\Facades\Route;
use jfsullivan\CommunityManager\Http\Controllers\ArticleController;
use jfsullivan\CommunityManager\Http\Controllers\CommunityController;
use jfsullivan\CommunityManager\Http\Controllers\CurrentCommunityController;
use jfsullivan\CommunityManager\Http\Controllers\MemberController;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\CommunityTransactionsPage;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberBalancesPage;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberTransactionHistoryPage;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberTransactionsPage;
use jfsullivan\CommunityManager\Livewire\Admin\Dashboard as CommunityAdminDashboard;
// use jfsullivan\CommunityManager\Livewire\Articles\CommunityArticlesIndexPage;
use jfsullivan\CommunityManager\Livewire\Articles\Index as ArticleIndex;
use jfsullivan\CommunityManager\Livewire\Articles\Show as ArticleShow;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Livewire\Pages\CreateCommunityPage;
use jfsullivan\MemberManager\Models\Invitation;
use jfsullivan\CommunityManager\Livewire\Articles\Pages\ArticleManagementPage as CommunityArticleManagementPage;
use jfsullivan\CommunityManager\Livewire\Articles\Pages\CreateArticlePage as CommunityArticleCreatePage;
use jfsullivan\CommunityManager\Livewire\Articles\Pages\EditArticlePage as CommunityArticleEditPage;
use jfsullivan\CommunityManager\Livewire\Articles\Pages\ShowArticlePage as CommunityArticleShowPage;

use jfsullivan\CommunityManager\Livewire\Memberships\Pages\MemberManagementPage as CommunityMemberManagementPage;

use jfsullivan\CommunityManager\Livewire\Articles\IndexPage as CommunityArticlesIndexPage;
use jfsullivan\CommunityManager\Livewire\Articles\ShowPage as CommunityArticlesShowPage;

use jfsullivan\CommunityManager\Livewire\Articles\EditPage as CommunityArticlesEditPage;

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
    // Route::middleware(['signed'])->group(function () {
    //     Route::get('community' . '-invitations/{invitation}', [OrganizationInvitationController::class, 'accept'])->name('organization-invitations.accept');
    // });

    Route::middleware(['auth', 'verified'])->group(function () {

        Route::put('/current-community', [CurrentCommunityController::class, 'update'])->name('current-community.update');

        Route::get('/communities/create', CreateCommunityPage::class)->name('communities.create');

        // Route::get('home', [HomeController::class, 'index'])->name('home');

        // Route::middleware(['site-admin'])->prefix('admin')->name('admin.site.')->group(function () {
        //     Route::get('/', [SiteAdminController::class, 'index'])->name('dashboard');
        // });

        // Route::get('communities/create', [OrganizationController::class, 'create'])->name('organizations.create');
        // Route::put('/current-'.'community', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');

        /**************************************************************************
         * Community Member Routes - community.*
         ***************************************************************************/
        Route::middleware(['community-member'])->prefix('community')->name('community.')->group(function () {
            Route::get('dashboard', config('community-manager.components.dashboard'))->name('dashboard');
            Route::get('create', config('community-manager.components.dashboard'))->name('create');

            Route::get('articles', CommunityArticlesIndexPage::class)->name('articles.index');
            Route::get('articles/{article_id}', CommunityArticlesShowPage::class)->name('articles.show');
            Route::get('articles/{article_id}/edit', CommunityArticlesEditPage::class)->name('articles.edit');
            // Route::get('articles/{id}', ArticleShow::class)->name('articles.show');
            // Communities
            // Route::get('community', [CommunityController::class, 'show'])->name('community.show');

            // Articles/News
            // Route::get('community/news/{article}', [ArticleController::class, 'show'])->name('organizations.articles.show');
            // Route::get('community/news', [ArticleController::class, 'index'])->name('organizations.articles.index');

            Route::get('members/{user_id}/transactions', MemberTransactionHistoryPage::class)->name('members.transactions');

            // Community Members
            Route::get('members', [MemberController::class, 'index'])->name('members.index');

            // Community Articles
            Route::get('articles', CommunityArticlesIndexPage::class)->name('articles.index');

            /**************************************************************************
             * Admin Routes - community.admin.*
             ***************************************************************************/
            Route::middleware(['community-admin'])->prefix('admin')->name('admin.')->group(function () {

                // Community Adminstration
                Route::get('/', config('community-manager.components.admin_dashboard'))->name('index');

                // Community Article Administration
                Route::get('articles', CommunityArticleManagementPage::class)->name('articles.index');
                Route::get('articles/create', CommunityArticleCreatePage::class)->name('articles.create');
                Route::get('articles/{article_id}', CommunityArticleShowPage::class)->name('articles.show');
                Route::get('articles/{article_id}/edit', CommunityArticleEditPage::class)->name('articles.edit');
                // Route::middleware(['community-owner'])->prefix('community/admin')->name('community.admin.')->group(function () {

                // Route::post('assets/upload', [AssetController::class, 'upload'])->name('assets.upload');

                // Organization Adminstration
                // Route::get('/', CommunityAdminDashboard::class)->name('index');
                //     Route::get('edit', [OrganizationController::class, 'edit'])->name('edit');

                // Community Member Administration
                Route::get('members', CommunityMemberManagementPage::class)->name('members.index');
                // Route::get('members', [MemberController::class, 'manage'])->name('members.manage');
                //     Route::get('members/invitations', [InvitationController::class, 'index'])->name('members.invitations');


                /**************************************************************************
                 * Community Accounting Routes - community.admin.accounting.*
                 ***************************************************************************/
                Route::prefix('accounting')->name('accounting.')->group(function () {
                    // Route::get('transactions', [OrganizationTransactionController::class, 'index'])->name('transactions.index');
                    // Route::get('accounting', [OrganizationAccountingController::class, 'manage'])->name('accounting.manage');
                    Route::get('/', CommunityTransactionsPage::class)->name('index');
                    Route::get('transactions', CommunityTransactionsPage::class)->name('transactions');
                    Route::get('member-balances', MemberBalancesPage::class)->name('member.balances');
                    Route::get('members/{user_id}/transactions', MemberTransactionsPage::class)->name('member.transactions');
                    // Route::get('accounting/members/{user_id}', [OrganizationAccountingController::class, 'show'])->name('accounting.transactions.manage');

                });
                //     // Organization Configuration
                //     // Route::get('invitations', [OrganizationAdminController::class, 'settings'])->name('invitations');
                //     Route::get('invitations', Invitations::class)->name('invitations');
                //     Route::get('settings', Settings::class)->name('settings');

                //     Route::get('/invitations/{id}/preview', function ($id) {
                //         $invitation = Invitation::make([
                //             'model_type' => 'organization',
                //             'model_id' => $id,
                //             'name' => 'John Smith',
                //             'email' => 'jsmith@testemail.com'
                //         ]);

                //         return (new OrganizationInvitation(auth()->user()->currentOrganization, $invitation));

                //     })->name('invitations.preview');
            });
        });
    });
});
