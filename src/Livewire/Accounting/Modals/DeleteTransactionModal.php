<?php

namespace jfsullivan\CommunityManager\Livewire\Accounting\Modals;

use jfsullivan\UiKit\Livewire\DeleteConfirmationModal;

class DeleteTransactionModal extends DeleteConfirmationModal
{
    public $modelType = 'transaction';

    public function deleteRecords() : bool
    {
        $transactionClass = app(config('community-manager.transaction_model'));

        return $transactionClass::whereIn('id', $this->records)->delete();
    }
}
