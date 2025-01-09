<?php

return [
    /*
     * The fully qualified class name of the user model.
     */
    'user_model' => jfsullivan\CommunityManager\Models\User::class,

    /*
     * The fully qualified class name of the community model.
     */
    'community_model' => jfsullivan\CommunityManager\Models\Community::class,

    /*
     * The fully qualified class name of the community policy.
     */
    'community_policy' => jfsullivan\CommunityManager\Policies\CommunityPolicy::class,

    /*
     * The fully qualified class name of the community transaction model.
     */
    'transaction_model' => jfsullivan\CommunityManager\Models\Transaction::class,

    /*
     * The fully qualified class name of the community transaction policy.
     */
    'transaction_policy' => jfsullivan\CommunityManager\Policies\TransactionPolicy::class,

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
        'dashboard' => jfsullivan\CommunityManager\Livewire\Dashboard::class,
        'admin_dashboard' => jfsullivan\CommunityManager\Livewire\Admin\Dashboard::class,
        'show_article' => jfsullivan\ArticleManager\Livewire\Articles\Show::class,
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
