<?php

namespace jfsullivan\CommunityManager\Models;

use Brick\Money\Formatter\MoneyNumberFormatter;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use jfsullivan\CommunityManager\Database\Factories\TransactionFactory;
use jfsullivan\CommunityManager\Enums\TransactionMethod;

/**
 * @property TransactionMethod|null $method
 */
class Transaction extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    protected $fillable = [
        'transacted_at',
        'community_id',
        'type_id',
        'user_id',
        'transfer_user_id',
        'model_type',
        'model_id',
        'amount',
        'description',
        'method',
        'created_by',
    ];

    protected $casts = [
        'transacted_at' => 'date:m/d/Y',
        'method' => TransactionMethod::class,
    ];

    // protected $appends = ['absolute_amount'];

    /**************************************************************************
     * Model Relationships
    ***************************************************************************/
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'type_id');
    }

    public function user()
    {
        $userClassName = config('community-manager.user_model');

        return $this->belongsTo($userClassName);
    }

    public function transferPartner()
    {
        $userClassName = config('community-manager.user_model');

        return $this->belongsTo($userClassName, 'transfer_user_id');
    }

    /**************************************************************************
     * Model Scopes
    ***************************************************************************/
    public function scopeWithRelatedInfo($query)
    {
        return $query;
    }

    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeSearch($query, ?string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $like = '%'.$term.'%';
            $query->where(function ($query) use ($term, $like) {
                $query->where('transactions.description', 'like', $like)
                    ->orWhereHas('user', fn ($query) => $query->searchByFullName($term))
                    ->orWhereRelation('type', 'name', 'like', $like);
            });
        });
    }

    /**************************************************************************
     * Mutators & Accessors
    ***************************************************************************/
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Money::ofMinor($value, 'USD'),
            set: function ($value) {
                return ($value instanceof Money)
                        ? $value->getMinorAmount()->toInt()
                        : Money::of($value, 'USD')->getMinorAmount()->toInt();
            },
        );
    }

    protected function absoluteAmountValue(): Attribute
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');

        return Attribute::make(
            get: fn ($value, $attributes) => (new MoneyNumberFormatter($formatter))->format(Money::ofMinor($attributes['amount'], 'USD')->abs()),
        );
    }

    protected function amountValue(): Attribute
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');

        return Attribute::make(
            get: fn ($value, $attributes) => (new MoneyNumberFormatter($formatter))->format(Money::ofMinor($attributes['amount'], 'USD')),
        );
    }

    // public function getAbsoluteAmountAttribute()
    // {
    //     return money($this->amount * 100)->absolute();
    //     // return money($this->amount * 100)->absolute()->formatByDecimal();
    // }

    // public function setAmountAttribute($value)
    // {
    //     $this->attributes['amount'] = $value * 100;
    // }
}
