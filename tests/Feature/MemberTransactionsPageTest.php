<?php

namespace jfsullivan\CommunityManager\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use jfsullivan\CommunityManager\Livewire\Accounting\Pages\MemberTransactionsPage;
use jfsullivan\CommunityManager\Models\Community;
use jfsullivan\CommunityManager\Models\Transaction;
use jfsullivan\CommunityManager\Models\TransactionType;
use jfsullivan\CommunityManager\Tests\TestCase;
use jfsullivan\CommunityManager\Tests\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class MemberTransactionsPageTest extends TestCase
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

    #[Test]
    public function it_sorts_by_transaction_description()
    {
        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
            'description' => 'Zebra fund contribution',
        ]);

        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
            'description' => 'Alpha payment',
        ]);

        $component = Livewire::test(MemberTransactionsPage::class, [
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
        ])
            ->call('sortBy', 'transaction', 'asc');

        $descriptions = collect($component->get('records')->items())->pluck('description')->all();
        $this->assertEquals(['Alpha payment', 'Zebra fund contribution'], $descriptions);
    }

    #[Test]
    public function it_sorts_by_type_name()
    {
        $depositType = TransactionType::find(2); // Deposit

        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $this->transactionType->id,
        ]);

        Transaction::factory()->create([
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
            'type_id' => $depositType->id,
        ]);

        $component = Livewire::test(MemberTransactionsPage::class, [
            'community_id' => $this->community->id,
            'user_id' => $this->user->id,
        ])
            ->call('sortBy', 'type', 'asc');

        $typeNames = collect($component->get('records')->items())->pluck('type.name')->all();
        $this->assertEquals(['Deposit', 'Withdrawal'], $typeNames);
    }
}
