<?php

use jfsullivan\ArticleManager\Livewire\Articles\Show;
use jfsullivan\CommunityManager\Livewire\Dashboard;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Models\User;
use jfsullivan\CommunityManager\Policies\CommunityPolicy;
use jfsullivan\CommunityManager\Policies\TransactionPolicy;

return [
    /*
     * The fully qualified class name of the user model.
     */
    'user_model' => User::class,

    /*
     * The fully qualified class name of the community model.
     */
    'community_model' => Community::class,

    /*
     * The fully qualified class name of the community policy.
     */
    'community_policy' => CommunityPolicy::class,

    /*
     * The fully qualified class name of the community transaction model.
     */
    'transaction_model' => Transaction::class,

    /*
     * The fully qualified class name of the community transaction type model.
     */
    'transaction_type_model' => TransactionType::class,

    /*
     * The fully qualified class name of the community transaction policy.
     */
    'transaction_policy' => TransactionPolicy::class,

    /*
     * The path to the admin layout for the community.
     */
    'admin_layout' => 'community-manager::components.layouts.admin',

    /**************************************************************************
     * Livewire Components
     *
     * These are the Livewire components that are used by Community Manager.
     * You can override these components and providing the new paths.
    ***************************************************************************/

    /*
     * The path to the community components.
     */
    'components' => [
        'dashboard' => Dashboard::class,
        'admin_dashboard' => jfsullivan\CommunityManager\Livewire\Admin\Dashboard::class,
        'show_article' => Show::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Features
    |--------------------------------------------------------------------------
    |
    | Some of the features provided by Community Manager are optional. Here you
    | may specify which features you would like to use.
    |
    */

    'features' => [
        // 'manage-members' => false,
        'track_member_balances' => false, // pre-requisite: manage-members = true
    ],
];
