<?php

it('serves manifest.json as valid JSON with required PWA fields', function () {
    $manifestPath = base_path('public/manifest.json');
    expect(file_exists($manifestPath))->toBeTrue();

    $manifest = json_decode(file_get_contents($manifestPath), true);
    expect($manifest)->not->toBeNull();
    expect($manifest)->toHaveKey('name');
    expect($manifest)->toHaveKey('short_name');
    expect($manifest)->toHaveKey('start_url');
    expect($manifest)->toHaveKey('display');
    expect($manifest)->toHaveKey('icons');
    expect($manifest['display'])->toBe('standalone');
});

it('serves sw.js service worker file', function () {
    $swPath = base_path('public/sw.js');
    expect(file_exists($swPath))->toBeTrue();

    $content = file_get_contents($swPath);
    expect($content)->toContain('CACHE_NAME');
    expect($content)->toContain('addEventListener');
});

it('service worker never caches API calls', function () {
    $swPath = base_path('public/sw.js');
    $content = file_get_contents($swPath);

    // The isApiCall function must exist and check for /api/ paths
    expect($content)->toContain('isApiCall');
    expect($content)->toContain('/api/');

    // Verify the pattern: API calls go to network only
    expect($content)->toContain('NEVER cache');
});
