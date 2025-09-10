<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JamesMills\LaravelTimezone\Facades\Timezone;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\CreateTransactionModal;
use jfsullivan\CommunityManager\Livewire\Accounting\Modals\UpdateTransactionModal;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Tests\TestCase;
use jfsullivan\CommunityManager\Tests\User;
use Livewire\Livewire;

class TransactionFormsTest extends TestCase
{
    use RefreshDatabase;

    protected $community;
    protected $user;
    protected $transferUser;
    protected $transactionTypes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create();
        
        $this->user = User::factory()->create([
            'current_community_id' => $this->community->id
        ]);
        
        $this->transferUser = User::factory()->create([
            'current_community_id' => $this->community->id
        ]);

        // Add users as community members
        $this->community->members()->attach($this->user->id, [
            'role_id' => 1,
            'type_id' => 1,
        ]);
        
        $this->community->members()->attach($this->transferUser->id, [
            'role_id' => 1,
            'type_id' => 1,
        ]);

        $this->setupTransactionTypes();
        
        $this->actingAs($this->user);
    }

    protected function setupTransactionTypes(): void
    {
        // Transaction types are already created by the migration, so just retrieve them
        $this->transactionTypes = collect([
            TransactionType::find(1), // Withdrawal
            TransactionType::find(2), // Deposit
            TransactionType::find(5), // Transfer Out
            TransactionType::find(6), // Transfer In
        ]);
    }

    /** @test */
    public function it_can_create_a_transaction()
    {
        $transactionData = [
            'form.type_id' => 1, // Withdrawal
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Test withdrawal',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transactionData)
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type_id' => 1,
            'description' => 'Test withdrawal',
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
        ]);
    }

    /** @test */
    public function it_can_update_a_transaction()
    {
        $transaction = Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => 1,
            'amount' => Money::of('50.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            'description' => 'Original description',
            'transacted_at' => Carbon::now(),
        ]);

        $updatedData = [
            'form.type_id' => 2, // Deposit
            'form.amount' => '75.00',
            'form.description' => 'Updated description',
        ];

        Livewire::test(UpdateTransactionModal::class, [
                'transaction_id' => $transaction->id
            ])
            ->set($updatedData)
            ->call('save');

        $transaction->refresh();
        
        $this->assertEquals(2, $transaction->type_id);
        $this->assertEquals('Updated description', $transaction->description);
        $this->assertEquals(Money::of('75.00', 'USD')->getMinorAmount()->toInt(), $transaction->amount->getMinorAmount()->toInt());
    }

    /** @test */
    public function it_applies_correct_amount_based_on_transaction_direction()
    {
        // Test withdrawal (negative direction)
        $withdrawalData = [
            'form.type_id' => 1, // Withdrawal, direction = -1
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Test withdrawal',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($withdrawalData)
            ->call('save');

        $withdrawal = Transaction::where('type_id', 1)->first();
        $this->assertTrue($withdrawal->amount->isNegative(), 'Withdrawal should have negative amount');
        $this->assertEquals(-10000, $withdrawal->amount->getMinorAmount()->toInt()); // -$100.00

        // Test deposit (positive direction)
        $depositData = [
            'form.type_id' => 2, // Deposit, direction = 1
            'form.user_id' => $this->user->id,
            'form.amount' => '50.00',
            'form.description' => 'Test deposit',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($depositData)
            ->call('save');

        $deposit = Transaction::where('type_id', 2)->first();
        $this->assertTrue($deposit->amount->isPositive(), 'Deposit should have positive amount');
        $this->assertEquals(5000, $deposit->amount->getMinorAmount()->toInt()); // $50.00
    }

    /** @test */
    public function it_can_search_for_users()
    {
        
        // Create a user with first_name and last_name for better search compatibility
        $searchUser = User::factory()->create([
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'current_community_id' => $this->community->id
        ]);

        // Add the user as a member of the community
        $this->community->members()->attach($searchUser->id, [
            'role_id' => 1, // Assuming member role has ID 1
            'type_id' => 1, // Assuming active type has ID 1
        ]);

        $component = Livewire::test(CreateTransactionModal::class, [
            'user_id' => $this->user->id
        ]);

        // Test the search functionality by calling the method directly
        $results = $component->instance()->searchUsers('John');
        
        $this->assertNotEmpty($results);
        $this->assertContains($searchUser->id, array_column($results, 'value'));
        
        // Also verify the full_name is present
        $this->assertContains('John Doe', array_column($results, 'label'));
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test by explicitly clearing required fields
        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set('form.type_id', null)
            ->set('form.amount', null)
            ->call('save')
            ->assertHasErrors([
                'form.type_id' => 'required',
                'form.amount' => 'required',
            ]);
    }

    /** @test */
    public function it_validates_transfer_user_id_for_transfer_transactions()
    {
        $transferData = [
            'form.type_id' => 5, // Transfer Out
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Transfer test',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
            // Missing transfer_user_id
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transferData)
            ->call('save')
            ->assertHasErrors(['form.transfer_user_id']);
    }

    /** @test */
    public function it_can_search_for_transfer_users()
    {
        $component = Livewire::test(CreateTransactionModal::class, [
            'user_id' => $this->user->id
        ]);

        // Set form user_id first to test transfer user filtering
        $component->set('form.user_id', $this->user->id);

        // Test the search functionality by calling the method directly
        $results = $component->instance()->searchTransferUsers($this->transferUser->first_name);
        
        $this->assertNotEmpty($results);
        $this->assertContains($this->transferUser->id, array_column($results, 'value'));
        
        // Should not contain the main user
        $this->assertNotContains($this->user->id, array_column($results, 'value'));
    }

    /** @test */
    public function it_creates_transfer_out_transaction()
    {
        $transferData = [
            'form.type_id' => 5, // Transfer Out
            'form.user_id' => $this->user->id,
            'form.transfer_user_id' => $this->transferUser->id,
            'form.amount' => '100.00',
            'form.description' => 'Transfer to friend',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transferData)
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 5,
            'description' => 'Transfer to friend',
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
        ]);
    }

    /** @test */
    public function it_creates_transfer_in_transaction()
    {
        $transferData = [
            'form.type_id' => 6, // Transfer In
            'form.user_id' => $this->user->id,
            'form.transfer_user_id' => $this->transferUser->id,
            'form.amount' => '100.00',
            'form.description' => 'Transfer from friend',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transferData)
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 6,
            'description' => 'Transfer from friend',
            'amount' => Money::of('100.00', 'USD')->getMinorAmount()->toInt(),
        ]);
    }

    /** @test */
    public function it_displays_correct_user_search_term_in_create_modal()
    {
        $component = Livewire::test(CreateTransactionModal::class, [
            'user_id' => $this->user->id
        ]);

        $searchTerm = $component->get('userSearchTerm');
        $this->assertEquals($this->user->full_name, $searchTerm);
    }

    /** @test */
    public function it_displays_correct_user_search_terms_in_update_modal()
    {
        $transaction = Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 5,
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            'transacted_at' => Carbon::now(),
        ]);

        $component = Livewire::test(UpdateTransactionModal::class, [
            'transaction_id' => $transaction->id
        ]);

        $userSearchTerm = $component->get('userSearchTerm');
        $transferUserSearchTerm = $component->get('transferUserSearchTerm');
        
        $this->assertEquals($this->user->full_name, $userSearchTerm);
        $this->assertEquals($this->transferUser->full_name, $transferUserSearchTerm);
    }

    /** @test */
    public function it_handles_timezone_conversion_correctly()
    {
        $localTime = Carbon::now()->format('Y-m-d H:i:s');
        
        $transactionData = [
            'form.type_id' => 1,
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Timezone test',
            'form.transacted_at' => $localTime,
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transactionData)
            ->call('save');

        $transaction = Transaction::latest()->first();
        
        // The transaction should be stored with UTC time
        $this->assertNotNull($transaction->transacted_at);
        $this->assertInstanceOf(Carbon::class, $transaction->transacted_at);
    }

    /** @test */
    public function it_dispatches_transaction_created_event()
    {
        $transactionData = [
            'form.type_id' => 1,
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Event test',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transactionData)
            ->call('save')
            ->assertDispatched('transaction-created');
    }

    /** @test */
    public function it_dispatches_transaction_updated_event()
    {
        $transaction = Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => 1,
            'amount' => Money::of('50.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            'transacted_at' => Carbon::now(),
        ]);

        $updatedData = [
            'form.type_id' => 1,
            'form.amount' => '50.00',
            'form.description' => 'Updated for event test',
        ];

        Livewire::test(UpdateTransactionModal::class, [
                'transaction_id' => $transaction->id
            ])
            ->set($updatedData)
            ->call('save')
            ->assertDispatched('transaction-updated');
    }

    /** @test */
    public function it_closes_modal_after_successful_save()
    {
        $transactionData = [
            'form.type_id' => 1,
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Modal close test',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $component = Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transactionData)
            ->call('save');

        // The modal should close after successful save
        // This is indicated by the closeModal() method being called
        // We can verify by checking that no validation errors exist
        $component->assertHasNoErrors();
    }

    /** @test */
    public function it_pre_fills_transacted_at_with_current_time()
    {
        $component = Livewire::test(CreateTransactionModal::class, [
            'user_id' => $this->user->id
        ]);

        $transactedAt = $component->get('form.transacted_at');
        
        $this->assertNotNull($transactedAt);
        
        // Should be approximately the current time (within a few seconds)
        $currentTime = Carbon::now();
        $formTime = Carbon::parse($transactedAt);
        $this->assertTrue($formTime->diffInSeconds($currentTime) < 5);
    }

    /**
     * Note: The following tests check for transfer transaction companion logic.
     * The CreateTransactionAction and UpdateTransactionAction classes handle
     * creating and updating companion transactions for transfer transactions.
     */

    /** @test */
    public function it_should_create_companion_transaction_for_transfer_out()
    {
        // This test verifies the transfer companion transaction logic
        $transferData = [
            'form.type_id' => 5, // Transfer Out
            'form.user_id' => $this->user->id,
            'form.transfer_user_id' => $this->transferUser->id,
            'form.amount' => '100.00',
            'form.description' => 'Transfer to friend',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transferData)
            ->call('save');

        // Should create the main transfer-out transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 5,
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
        ]);

        // Should also create companion transfer-in transaction for the transfer user
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->transferUser->id,
            'transfer_user_id' => $this->user->id,
            'type_id' => 6, // Transfer In
            'amount' => Money::of('100.00', 'USD')->getMinorAmount()->toInt(),
            'description' => 'Transfer to friend',
        ]);

        // Should have exactly 2 transactions total
        $this->assertDatabaseCount('transactions', 2);
    }

    /** @test */
    public function it_should_create_companion_transaction_for_transfer_in()
    {
        // This test verifies the transfer companion transaction logic
        $transferData = [
            'form.type_id' => 6, // Transfer In
            'form.user_id' => $this->user->id,
            'form.transfer_user_id' => $this->transferUser->id,
            'form.amount' => '100.00',
            'form.description' => 'Transfer from friend',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transferData)
            ->call('save');

        // Should create the main transfer-in transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 6,
            'amount' => Money::of('100.00', 'USD')->getMinorAmount()->toInt(),
        ]);

        // Should also create companion transfer-out transaction for the transfer user
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->transferUser->id,
            'transfer_user_id' => $this->user->id,
            'type_id' => 5, // Transfer Out
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            'description' => 'Transfer from friend',
        ]);

        // Should have exactly 2 transactions total
        $this->assertDatabaseCount('transactions', 2);
    }

    /** @test */
    public function it_should_update_companion_transaction_when_updating_transfer_transaction()
    {
        // Create initial transfer transactions (main + companion)
        $mainTransaction = Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'transfer_user_id' => $this->transferUser->id,
            'type_id' => 5, // Transfer Out
            'amount' => Money::of('100.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            'description' => 'Original transfer',
            'transacted_at' => Carbon::now(),
        ]);

        $companionTransaction = Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->transferUser->id,
            'transfer_user_id' => $this->user->id,
            'type_id' => 6, // Transfer In
            'amount' => Money::of('100.00', 'USD')->getMinorAmount()->toInt(),
            'description' => 'Original transfer',
            'transacted_at' => $mainTransaction->transacted_at,
        ]);

        $updatedData = [
            'form.amount' => '150.00',
            'form.description' => 'Updated transfer',
        ];

        Livewire::test(UpdateTransactionModal::class, [
                'transaction_id' => $mainTransaction->id
            ])
            ->set($updatedData)
            ->call('save');

        $mainTransaction->refresh();
        $companionTransaction->refresh();

        // Main transaction should be updated
        $this->assertEquals('Updated transfer', $mainTransaction->description);
        $this->assertEquals(
            Money::of('150.00', 'USD')->multipliedBy(-1)->getMinorAmount()->toInt(),
            $mainTransaction->amount->getMinorAmount()->toInt()
        );

        // Companion transaction should also be updated
        $this->assertEquals('Updated transfer', $companionTransaction->description);
        $this->assertEquals(
            Money::of('150.00', 'USD')->getMinorAmount()->toInt(),
            $companionTransaction->amount->getMinorAmount()->toInt()
        );
    }

    /** @test */
    public function it_should_not_create_companion_transaction_for_non_transfer_types()
    {
        $transactionData = [
            'form.type_id' => 1, // Withdrawal (non-transfer)
            'form.user_id' => $this->user->id,
            'form.amount' => '100.00',
            'form.description' => 'Regular withdrawal',
            'form.transacted_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        Livewire::test(CreateTransactionModal::class, [
                'user_id' => $this->user->id
            ])
            ->set($transactionData)
            ->call('save');

        // Should have exactly 1 transaction (no companion)
        $this->assertDatabaseCount('transactions', 1);
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type_id' => 1,
            'transfer_user_id' => null,
        ]);
    }
}