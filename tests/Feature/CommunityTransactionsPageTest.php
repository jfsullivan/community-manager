<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\CommunityTransactionsPage;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Tests\TestCase;
use jfsullivan\CommunityManager\Tests\User;
use Livewire\Livewire;

class CommunityTransactionsPageTest extends TestCase
{
    use RefreshDatabase;

    protected $community;

    protected $user;

    protected $transactionType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create();

        $this->user = User::factory()->create([
            'current_community_id' => $this->community->id,
        ]);

        // Add user as community member
        $this->community->members()->attach($this->user->id, [
            'role_id' => 1,
            'type_id' => 1,
        ]);

        $this->transactionType = TransactionType::find(1); // Withdrawal

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_displays_transactions_page()
    {
        Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->assertViewIs('community-manager::livewire.accounting.pages.community-transactions-page')
            ->assertOk();
    }

    /** @test */
    public function it_paginates_transactions_correctly()
    {
        // Create more transactions than the default per page (100)
        Transaction::factory()->count(150)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ]);

        // Check that initial page loads with transactions
        $records = $component->get('records');
        $this->assertNotEmpty($records->items());

        // The default perPage is 100, so we should get 100 transactions on first page
        $this->assertLessThanOrEqual(100, count($records->items()));

        // Should have pagination links since we have more than 100 transactions
        $this->assertTrue($records->hasPages());
    }

    /** @test */
    public function it_changes_per_page_setting()
    {
        // Create enough transactions to test pagination
        Transaction::factory()->count(50)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->set('perPage', 10);

        $records = $component->get('records');

        // With perPage set to 10, we should get at most 10 transactions
        $this->assertLessThanOrEqual(10, count($records->items()));

        // Should have more pages since we have 50 transactions but only showing 10
        $this->assertTrue($records->hasPages());
    }

    /** @test */
    public function it_navigates_to_next_page()
    {
        // Create transactions with specific data to verify different pages
        $transactions = Transaction::factory()->count(25)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->set('perPage', 10);

        // Get first page results
        $firstPageRecords = $component->get('records');
        $firstPageItems = $firstPageRecords->items();

        $this->assertCount(10, $firstPageItems);
        $this->assertTrue($firstPageRecords->hasPages());

        // Get the cursor for next page and navigate
        if ($firstPageRecords->hasMorePages()) {
            $nextCursor = $firstPageRecords->nextCursor();
            
            $component->call('nextPage', $nextCursor->encode());
            
            $secondPageRecords = $component->get('records');
            $secondPageItems = $secondPageRecords->items();

            // Should get different transactions on second page
            $this->assertCount(10, $secondPageItems);

            // Verify that the items are different (different IDs)
            $firstPageIds = collect($firstPageItems)->pluck('id')->sort()->values();
            $secondPageIds = collect($secondPageItems)->pluck('id')->sort()->values();

            $this->assertNotEquals($firstPageIds, $secondPageIds);
        }
    }

    /** @test */
    public function it_filters_transactions_and_maintains_pagination()
    {
        // Create transactions with different types
        $withdrawalType = TransactionType::find(1); // Withdrawal
        $depositType = TransactionType::find(2); // Deposit

        Transaction::factory()->count(30)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $withdrawalType->id,
        ]);

        Transaction::factory()->count(20)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $depositType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->set('perPage', 10)
            ->set('transactionTypeFilter', $withdrawalType->id);

        $filteredRecords = $component->get('records');

        // Should only get withdrawal transactions
        $this->assertLessThanOrEqual(10, count($filteredRecords->items()));

        // Verify all returned items are withdrawal type
        foreach ($filteredRecords->items() as $transaction) {
            $this->assertEquals($withdrawalType->id, $transaction->type_id);
        }

        // Should still have pagination since we have 30 withdrawals but only showing 10
        $this->assertTrue($filteredRecords->hasPages());
    }

    /** @test */
    public function it_searches_transactions_and_maintains_pagination()
    {
        // Create transactions with searchable descriptions
        Transaction::factory()->count(20)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
            'description' => 'Test payment for services',
        ]);

        Transaction::factory()->count(15)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
            'description' => 'Monthly subscription fee',
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->set('perPage', 10)
            ->set('searchFilter', 'payment');

        $searchResults = $component->get('records');

        // Should only get transactions with "payment" in description
        $this->assertLessThanOrEqual(10, count($searchResults->items()));

        // Should have pagination since we have 20 matching transactions but only showing 10
        $this->assertTrue($searchResults->hasPages());
    }

    /** @test */
    public function it_sorts_transactions_and_maintains_pagination()
    {
        // Create transactions with different dates
        Transaction::factory()->count(30)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ])
            ->set('perPage', 10)
            ->call('sortBy', 'date', 'asc');

        $sortedRecords = $component->get('records');

        // Should get 10 transactions
        $this->assertCount(10, $sortedRecords->items());

        // Should maintain pagination with sorting
        $this->assertTrue($sortedRecords->hasPages());

        // Verify sorting - first item should be older than or equal to second item
        $items = $sortedRecords->items();
        if (count($items) >= 2) {
            $firstDate = $items[0]->transacted_at;
            $secondDate = $items[1]->transacted_at;
            $this->assertTrue($firstDate <= $secondDate, 'Items should be sorted by date ascending');
        }
    }

    /** @test */
    public function it_displays_community_balance()
    {
        // Create some transactions with different amounts
        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => 1, // Withdrawal (-1 direction)
            'amount' => -5000, // -$50.00
        ]);

        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => 2, // Deposit (+1 direction)
            'amount' => 10000, // +$100.00
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ]);

        $memberBalance = $component->get('memberBalance');
        $this->assertNotNull($memberBalance);

        // Should have a collection with balance totals
        $this->assertNotEmpty($memberBalance);
    }

    /** @test */
    public function it_filters_by_community_correctly()
    {
        // Create another community with transactions
        $otherCommunity = Community::factory()->create();

        Transaction::factory()->count(10)->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        Transaction::factory()->count(5)->create([
            'community_id' => $otherCommunity->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        $component = Livewire::test(CommunityTransactionsPage::class, [
            'community_id' => $this->community->id,
        ]);

        $records = $component->get('records');

        // Should only get transactions from our community
        $this->assertCount(10, $records->items());

        // Verify all transactions belong to the correct community
        foreach ($records->items() as $transaction) {
            $this->assertEquals($this->community->id, $transaction->community_id);
        }
    }
}
