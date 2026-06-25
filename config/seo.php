<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SEO Meta
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'title' => [
            'en' => 'eFirm — Legal Practice Platform for the Levant',
            'ar' => 'إي فيرم — منصة إدارة مكاتب المحاماة في بلاد الشام',
        ],
        'description' => [
            'en' => 'Run your law firm on one workspace: matters, documents, billing, and Arabic-native AI. PDPL-compliant. From $20/seat/month.',
            'ar' => 'أدِر مكتبك على منصة واحدة: ملفات القضايا والمستندات والفواتير وذكاء اصطناعي يفهم العربية. متوافق مع قانون حماية البيانات الشخصية. ابتداءً من 20 دولاراً للمستخدم شهرياً.',
        ],
        'og_image' => '/img/og-image.jpg',
        'og_image_fallback' => '/img/og-fallback.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON-LD Structured Data
    |--------------------------------------------------------------------------
    */
    'json_ld' => [
        '@context' => 'https://schema.org',
        '@type' => 'LegalService',
        'name' => 'eFirm',
        'description' => 'AI-native legal practice management platform for Levant law firms.',
        'url' => 'https://efirm.io',
        'areaServed' => [
            ['@type' => 'Country', 'name' => 'Jordan'],
            ['@type' => 'Country', 'name' => 'Lebanon'],
            ['@type' => 'Country', 'name' => 'Palestine'],
            ['@type' => 'Country', 'name' => 'Iraq'],
        ],
        'priceRange' => '$20-$30',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Amman',
            'addressCountry' => 'JO',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name' => 'eFirm',
            'url' => 'https://efirm.io',
        ],
    ],

];
