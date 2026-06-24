<?php

/**
 * Locale key parity for all SURGE-DASH-01 lang files.
 * Enforces CLAUDE.md §8: key parity between ar/*.php and en/*.php.
 */
$dashboardLangFiles = ['shell', 'dashboard', 'brand', 'common'];

foreach ($dashboardLangFiles as $file) {
    test("{$file}.php has identical key structure in AR and EN", function () use ($file) {
        $en = require resource_path("lang/en/{$file}.php");
        $ar = require resource_path("lang/ar/{$file}.php");

        expect(array_keys($en))->toBe(array_keys($ar),
            "Key mismatch in {$file}.php — EN keys: ".implode(', ', array_keys($en))
            .' vs AR keys: '.implode(', ', array_keys($ar))
        );
    });
}

test('no Arabic-Indic numerals in any AR dashboard lang file', function () {
    $arFiles = glob(resource_path('lang/ar/*.php'));

    foreach ($arFiles as $file) {
        $content = file_get_contents($file);
        $basename = basename($file);

        expect(preg_match('/[\x{0660}-\x{0669}]/u', $content))
            ->toBe(0, "Arabic-Indic numeral found in {$basename}");
    }
});

test('dashboard layout does not reference Google Fonts', function () {
    $layout = file_get_contents(resource_path('views/layouts/dashboard.blade.php'));

    expect($layout)->not->toContain('fonts.googleapis.com');
    expect($layout)->not->toContain('fonts.gstatic.com');
});

test('dashboard layout uses brand theme color', function () {
    $layout = file_get_contents(resource_path('views/layouts/dashboard.blade.php'));

    expect($layout)->toContain('#072E17');
    expect($layout)->not->toContain('#2563eb');
});
