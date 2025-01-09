<?php

namespace jfsullivan\CommunityManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use jfsullivan\CommunityManager\Database\Factories\TransactionTypeFactory;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TransactionType extends Model
{
    use HasFactory;
    use HasSlug;

    protected static function newFactory()
    {
        return TransactionTypeFactory::new();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function isTransferTransaction()
    {
        return $this->slug === 'transfer-in' || $this->slug === 'transfer-out';
    }
}
