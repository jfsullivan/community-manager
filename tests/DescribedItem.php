<?php

namespace jfsullivan\CommunityManager\Tests;

use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Contracts\DescribesTransaction;

/** A transaction's model that says what the money was for (DescribesTransaction tests). */
class DescribedItem extends Model implements DescribesTransaction
{
    protected $table = 'described_items';

    protected $guarded = [];

    public $timestamps = false;

    public function transactionTitle(): ?string
    {
        return $this->title;
    }

    public function transactionSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function transactionTypeDetail(): ?string
    {
        return $this->type_detail;
    }
}
