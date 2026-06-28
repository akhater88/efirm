<?php

test('all brand SVG assets exist on disk', function () {
    collect([
        'img/brand/efirm-logo.svg',
        'img/brand/efirm-logo-reversed.svg',
        'img/brand/efirm-mark.svg',
        'img/brand/efirm-mark-reversed.svg',
        'img/brand/efirm-favicon.svg',
    ])->each(fn ($p) => expect(file_exists(public_path($p)))->toBeTrue("Missing: {$p}"));
});

test('all brand PNG assets exist on disk', function () {
    collect([
        'img/brand/efirm-favicon-16.png',
        'img/brand/efirm-favicon-32.png',
        'img/brand/efirm-favicon-48.png',
        'img/brand/efirm-favicon-192.png',
        'img/brand/efirm-favicon-512.png',
    ])->each(fn ($p) => expect(file_exists(public_path($p)))->toBeTrue("Missing: {$p}"));
});

test('all font files exist on disk', function () {
    collect([
        'fonts/playfair-display-v30-latin-700.woff2',
        'fonts/source-sans-pro-v21-latin-regular.woff2',
        'fonts/source-sans-pro-v21-latin-500.woff2',
        'fonts/source-sans-pro-v21-latin-600.woff2',
        'fonts/source-sans-pro-v21-latin-700.woff2',
        'fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2',
        'fonts/ibm-plex-sans-arabic-v12-arabic-500.woff2',
        'fonts/ibm-plex-sans-arabic-v12-arabic-600.woff2',
        'fonts/ibm-plex-sans-arabic-v12-arabic-700.woff2',
    ])->each(fn ($p) => expect(file_exists(public_path($p)))->toBeTrue("Missing: {$p}"));
});

test('tokens.json is valid JSON', function () {
    $path = resource_path('design/tokens.json');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    $decoded = json_decode($content, true);
    expect(json_last_error())->toBe(JSON_ERROR_NONE);
    expect($decoded)->toHaveKey('color');
    expect($decoded)->toHaveKey('fontFamily');
    expect($decoded)->toHaveKey('fontSize');
    expect($decoded)->toHaveKey('_meta');
    expect($decoded['_meta']['surge'])->toBe('SURGE-DASH-01');
});

test('brand lang files exist for both locales', function () {
    expect(file_exists(resource_path('lang/en/brand.php')))->toBeTrue();
    expect(file_exists(resource_path('lang/ar/brand.php')))->toBeTrue();

    $en = require resource_path('lang/en/brand.php');
    $ar = require resource_path('lang/ar/brand.php');

    expect(array_keys($en))->toBe(array_keys($ar));
    expect($en['app_name'])->toBe('eFirm');
    expect($ar['app_name'])->toBe('eFirm');
});

test('no Arabic-Indic numerals in AR brand lang file', function () {
    $content = file_get_contents(resource_path('lang/ar/brand.php'));
    // Arabic-Indic digits: ٠١٢٣٤٥٦٧٨٩
    expect(preg_match('/[\x{0660}-\x{0669}]/u', $content))->toBe(0);
});

test('layout head contains font preload and favicon links', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($layout)->toContain('fonts/source-sans-pro-v21-latin-regular.woff2');
    expect($layout)->toContain('fonts/ibm-plex-sans-arabic-v12-arabic-regular.woff2');
    expect($layout)->toContain('efirm-favicon.svg');
    expect($layout)->toContain('efirm-favicon-32.png');
    expect($layout)->toContain('theme-color');
    expect($layout)->toContain('#330000');
});
