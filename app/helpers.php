<?php

if (! function_exists('money')) {
    /** Format an amount with the shop currency, e.g. money(1500) => "1,500.00 EGP" */
    function money($amount): string
    {
        return number_format((float) $amount, 2).' '.config('shop.currency');
    }
}
