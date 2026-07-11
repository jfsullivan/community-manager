<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use jfsullivan\ApexUi\Modal\DeleteConfirmationModal;

class DeleteTransactionModal extends DeleteConfirmationModal
{
    public string $modalName = 'delete-transaction';

    public $modelType = 'transaction';

    public function deleteRecords(): bool
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::whereIn('id', $this->records)->delete();
    }
}
