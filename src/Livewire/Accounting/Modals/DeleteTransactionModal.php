<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use Illuminate\Support\Facades\Gate;
use jfsullivan\ApexUi\Modal\DeleteConfirmationModal;
use jfsullivan\CommunityManager\Livewire\Concerns\ResolvesCurrentCommunity;

class DeleteTransactionModal extends DeleteConfirmationModal
{
    use ResolvesCurrentCommunity;

    public string $modalName = 'delete-transaction';

    public $modelType = 'transaction';

    /**
     * Only the current community's transactions, and only for someone allowed
     * to delete them there. The modal is mounted on member-facing pages, so the
     * ids it's opened with can't be trusted.
     */
    public function checkAuthorization(): bool
    {
        $community = $this->currentCommunity();
        $transactionClass = app(config('community-manager.transaction_model'));

        $allowed = $community !== null
            && Gate::allows('delete-community-transaction', [$community])
            && $transactionClass::whereIn('id', $this->records)->where('community_id', '!=', $community->id)->doesntExist();

        if (! $allowed) {
            $this->dispatch('notify', type: 'error', title: 'Not allowed', message: 'You can’t delete these transactions.');
            $this->closeModal();
        }

        return $allowed;
    }

    public function deleteRecords(): bool
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return (bool) $transactionClass::whereIn('id', $this->records)
            ->where('community_id', $this->currentCommunity()?->id)
            ->delete();
    }
}
