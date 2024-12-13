<?php

namespace jfsullivan\CommunityManager\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use jfsullivan\CommunityManager\Database\Factories\TransactionFactory;

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
        'created_by',
    ];

    protected $casts = [
        'transacted_at' => 'date:m/d/Y',
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

    public function scopeSearch($query, string $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%'.$term.'%';
            $query->where(function ($query) use ($term) {
                $query->where('description', 'like', $term)
                    ->orWhereRelation('user', 'name', 'like', $term)
                    ->orWhereRelation('type', 'name', 'like', $term);
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
                return ($value instanceOf Money) 
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
            get: fn ($value, $attributes) => Money::ofMinor($attributes['amount'], 'USD')->abs()->formatWith($formatter),
        );
    }

    protected function amountValue(): Attribute
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, '');

        return Attribute::make(
            get: fn ($value, $attributes) => Money::ofMinor($attributes['amount'], 'USD')->formatWith($formatter),
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
