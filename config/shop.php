<?php

/*
|--------------------------------------------------------------------------
| Shop settings — edit these values from your .env file
|--------------------------------------------------------------------------
*/

return [
    'name' => env('SHOP_NAME', 'AURA'),
    'currency' => env('SHOP_CURRENCY', 'EGP'),
    'shipping_fee' => (float) env('SHOP_SHIPPING_FEE', 60),
    'free_shipping_over' => (float) env('SHOP_FREE_SHIPPING_OVER', 3000),
    'email' => env('SHOP_EMAIL', 'hello@aura.test'),
    'phone' => env('SHOP_PHONE', '+20 100 000 0000'),
    'address' => env('SHOP_ADDRESS', 'Cairo, Egypt'),
    'whatsapp' => env('SHOP_WHATSAPP', '201000000000'),

    'social' => [
        'facebook' => env('SHOP_FACEBOOK', '#'),
        'instagram' => env('SHOP_INSTAGRAM', '#'),
        'twitter' => env('SHOP_TWITTER', '#'),
    ],

    // Optional: drop a video at public/videos/intro.mp4 to replace the animated intro
    'intro_video' => env('SHOP_INTRO_VIDEO', 'videos/intro.mp4'),
];
