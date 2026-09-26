<?php

namespace jfsullivan\CommunityManager\Enums;

/**
 * How money moved for a deposit or withdrawal (the community's own ledger
 * doesn't move money; the admin records how it happened).
 */
enum TransactionMethod: string
{
    case Venmo = 'venmo';
    case PayPal = 'paypal';
    case Zelle = 'zelle';
    case Cash = 'cash';
    case Check = 'check';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Venmo => 'Venmo',
            self::PayPal => 'PayPal',
            self::Zelle => 'Zelle',
            self::Cash => 'Cash',
            self::Check => 'Check',
            self::Other => 'Other',
        };
    }

    /**
     * Transaction type slugs that record a method: money actually changing
     * hands with the community.
     *
     * @return array<int, string>
     */
    public static function appliesToTypes(): array
    {
        return ['deposit', 'withdrawal'];
    }

    /** @return array<int, array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(fn (self $method) => ['value' => $method->value, 'label' => $method->label()], self::cases());
    }
}
