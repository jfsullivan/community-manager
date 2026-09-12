<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\CreateTransactionModal;
use jfsullivan\CommunityManager\Livewire\Filters\MemberStatusFilter;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Tests\TestCase;
use jfsullivan\CommunityManager\Tests\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class MemberStatusScopingTest extends TestCase
{
    use RefreshDatabase;

    protected $community;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create(['track_member_balances' => true]);

        $this->admin = User::factory()->create(['current_community_id' => $this->community->id]);
        $this->actingAs($this->admin);
    }

    protected function attachMember(User $user, array $pivot = []): void
    {
        $this->community->members()->attach($user->id, array_merge([
            'role_id' => 1,
            'type_id' => 1,
            'start_at' => now()->subMonth(),
        ], $pivot));
    }

    protected function makeCurrent(string $name): User
    {
        $user = User::factory()->create(['name' => $name, 'first_name' => $name, 'last_name' => 'Current']);
        $this->attachMember($user);

        return $user;
    }

    protected function makeFormer(string $name): User
    {
        $user = User::factory()->create(['name' => $name, 'first_name' => $name, 'last_name' => 'Former']);
        $this->attachMember($user, ['start_at' => now()->subYear(), 'end_at' => now()->subMonth()]);

        return $user;
    }

    protected function makeBanned(string $name): User
    {
        $user = User::factory()->create(['name' => $name, 'first_name' => $name, 'last_name' => 'Banned']);
        $this->attachMember($user, ['start_at' => now()->subYear(), 'end_at' => now()->subMonth()]);

        $this->community->memberships()->where('user_id', $user->id)->first()->setStatus('banned', 'test');

        return $user;
    }

    #[Test]
    public function the_transaction_modal_user_select_excludes_former_and_banned_members()
    {
        $current = $this->makeCurrent('Carla');
        $former = $this->makeFormer('Fiona');
        $banned = $this->makeBanned('Bruno');

        $component = Livewire::test(CreateTransactionModal::class);

        $values = array_column($component->instance()->userOptions, 'value');

        $this->assertContains($current->id, $values);
        $this->assertNotContains($former->id, $values);
        $this->assertNotContains($banned->id, $values);
    }

    #[Test]
    public function the_member_status_filter_defaults_to_current()
    {
        // The balances page's balance scope relies on staudenmeir/laravel-cte
        // (withExpression), only present in the host app, so the page can't be
        // rendered in this harness. The MemberBalancesPage composes this trait,
        // whose default drives the "hide former/banned by default" behaviour.
        $withFilter = new class
        {
            use MemberStatusFilter;
        };

        $this->assertSame('current', $withFilter->memberStatusFilter);
    }

    /**
     * Runs the MemberStatusFilter query scope in isolation (no balance CTE) and
     * returns the matching users, so we can assert each status segment without
     * the host-app-only withExpression() dependency.
     *
     * @return array<int, int> matching user ids
     */
    protected function filteredUserIds(string $status): array
    {
        $filter = new class
        {
            use MemberStatusFilter;
        };

        $filter->memberStatusFilter = $status;

        $query = User::query()->select('users.id');

        return $filter->applyMemberStatusFilter($query, $this->community)->pluck('id')->all();
    }

    #[Test]
    public function the_current_status_filter_returns_only_current_members()
    {
        $current = $this->makeCurrent('Carla');
        $former = $this->makeFormer('Fiona');
        $banned = $this->makeBanned('Bruno');

        $ids = $this->filteredUserIds('current');

        $this->assertContains($current->id, $ids);
        $this->assertNotContains($former->id, $ids);
        $this->assertNotContains($banned->id, $ids);
    }

    #[Test]
    public function the_former_status_filter_returns_ended_members_but_not_banned()
    {
        $current = $this->makeCurrent('Carla');
        $former = $this->makeFormer('Fiona');
        $banned = $this->makeBanned('Bruno');

        $ids = $this->filteredUserIds('former');

        $this->assertContains($former->id, $ids);
        $this->assertNotContains($current->id, $ids);
        $this->assertNotContains($banned->id, $ids, 'Banned members get their own segment.');
    }

    #[Test]
    public function the_banned_status_filter_returns_only_banned_members()
    {
        $current = $this->makeCurrent('Carla');
        $former = $this->makeFormer('Fiona');
        $banned = $this->makeBanned('Bruno');

        $ids = $this->filteredUserIds('banned');

        $this->assertContains($banned->id, $ids);
        $this->assertNotContains($current->id, $ids);
        $this->assertNotContains($former->id, $ids);
    }

    #[Test]
    public function the_pending_status_filter_returns_only_unconfirmed_members()
    {
        $current = $this->makeCurrent('Carla');

        $pending = User::factory()->create(['name' => 'Petra', 'first_name' => 'Petra', 'last_name' => 'Pending']);
        $this->attachMember($pending, ['start_at' => null]);

        $ids = $this->filteredUserIds('pending');

        $this->assertContains($pending->id, $ids);
        $this->assertNotContains($current->id, $ids);
    }
}
