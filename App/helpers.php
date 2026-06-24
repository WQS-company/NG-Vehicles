<?php

if (!function_exists('format_currency')) {
    /**
     * Format number as Nigerian Naira currency with symbol
     * @param float|int $amount
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, $decimals = 2) {
        if ($amount === null || $amount === '') return '₦0.00';
        return '₦' . number_format((float)$amount, $decimals);
    }
}
