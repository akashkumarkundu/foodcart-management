<?php

namespace App\Helpers;

class Bdt
{
    /**
     * Format amount in Bangladeshi Taka (e.g. ৳12,500)
     */
    public static function format(mixed $amount, int $decimals = 2): string
    {
        $num = (float) $amount;

        // If decimal part is zero and decimals > 0, we can show clean integer or standard 2 decimals
        return '৳'.number_format($num, $decimals);
    }

    /**
     * Format without decimal if whole number
     */
    public static function clean(mixed $amount): string
    {
        $num = (float) $amount;

        if (floor($num) == $num) {
            return '৳'.number_format($num, 0);
        }

        return '৳'.number_format($num, 2);
    }

    /**
     * Currency symbol
     */
    public static function symbol(): string
    {
        return '৳';
    }
}
