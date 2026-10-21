<?php

namespace jfsullivan\CommunityManager\Contracts;

/**
 * Implemented by the model a transaction belongs to (its `model` morph, e.g.
 * a contest entry or a pool entry) so the ledger can say what the money was
 * for. Transactions whose model doesn't implement it show their description.
 */
interface DescribesTransaction
{
    /** The main line in the ledger's detail column, e.g. the contest or pool name. */
    public function transactionTitle(): ?string;

    /** An optional second line, e.g. the event or the entry's name. */
    public function transactionSubtitle(): ?string;

    /** An optional line under the transaction type, e.g. "Round 2". */
    public function transactionTypeDetail(): ?string;
}
