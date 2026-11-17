<?php

namespace jfsullivan\CommunityManager;

use Illuminate\Support\Number;

class CommunityManager
{
    /** The ISO 4217 code member accounting uses (config('community-manager.currency'), USD by default). */
    public static function currency(): string
    {
        return strtoupper((string) config('community-manager.currency', 'USD')) ?: 'USD';
    }

    /** A cent amount for display: -1234 => "-$12.34" in USD; other currencies use their own symbol. */
    public static function formatCents(int $cents): string
    {
        $currency = static::currency();

        if ($currency === 'USD') {
            return ($cents < 0 ? '-' : '').'$'.number_format(abs($cents) / 100, 2);
        }

        return (string) Number::currency($cents / 100, in: $currency);
    }
}
