<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberTransactionHistoryPage;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Tests\TestCase;
use jfsullivan\CommunityManager\Tests\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class MemberTransactionHistoryPageTest extends TestCase
{
    use RefreshDatabase;

    protected $community;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create();

        $this->user = User::factory()->create([
            'current_community_id' => $this->community->id,
        ]);

        $this->community->members()->attach($this->user->id, [
            'role_id' => 1,
            'type_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_filters_by_several_transaction_types_then_clears_them()
    {
        $create = fn (int $typeId) => Transaction::factory()->create([
            'community_id' => $this->community->id, 'user_id' => $this->user->id, 'type_id' => $typeId,
        ]);
        $withdrawal = $create(1);
        $deposit = $create(2);
        $entryFee = $create(3);

        $page = Livewire::test(MemberTransactionHistoryPage::class, ['community_id' => $this->community->id]);
        $ids = fn () => collect($page->get('records')->items())->pluck('id')->all();

        $page->set('transactionTypeFilter', ['withdrawal', 'entry-fee']);
        $this->assertEqualsCanonicalizing([$withdrawal->id, $entryFee->id], $ids());

        $page->call('clearAllFilters')->assertSet('transactionTypeFilter', []);
        $this->assertEqualsCanonicalizing([$withdrawal->id, $deposit->id, $entryFee->id], $ids());
    }

    #[Test]
    public function it_loads_an_old_single_slug_or_numeric_id_link_as_a_one_element_list()
    {
        Livewire::withQueryParams(['transactionTypeFilter' => 'deposit'])
            ->test(MemberTransactionHistoryPage::class, ['community_id' => $this->community->id])
            ->assertSet('transactionTypeFilter', ['deposit']);

        Livewire::withQueryParams(['transactionTypeFilter' => '1'])
            ->test(MemberTransactionHistoryPage::class, ['community_id' => $this->community->id])
            ->assertSet('transactionTypeFilter', ['withdrawal']);
    }

    #[Test]
    public function the_type_filter_is_the_primary_filter_and_not_counted_in_the_menu()
    {
        $page = Livewire::test(MemberTransactionHistoryPage::class, ['community_id' => $this->community->id])
            ->set('transactionTypeFilter', ['deposit', 'withdrawal']);

        $this->assertSame(['deposit', 'withdrawal'], $page->instance()->transactionTypeSlugs());
        $this->assertSame(0, $page->instance()->menuFilterCount());

        $page->set('transactionTypeFilter', []);
        $this->assertSame([], $page->instance()->transactionTypeSlugs());
    }
}
