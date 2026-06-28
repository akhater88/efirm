<?php

/**
 * WCAG AA contrast ratio validation for all defined text/background pairings.
 *
 * Per WCAG 2.1 SC 1.4.3: body text requires 4.5:1, large text requires 3:1.
 */
function hexToRelativeLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
    $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
    $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

function contrastRatio(string $fg, string $bg): float
{
    $l1 = hexToRelativeLuminance($fg);
    $l2 = hexToRelativeLuminance($bg);

    $lighter = max($l1, $l2);
    $darker = min($l1, $l2);

    return ($lighter + 0.05) / ($darker + 0.05);
}

// Body text (4.5:1 minimum)
test('text-primary on surface-card meets WCAG AA body', function () {
    expect(contrastRatio('#1C1917', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('text-secondary on surface-card meets WCAG AA body', function () {
    expect(contrastRatio('#44403C', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('text-tertiary on surface-card meets WCAG AA body', function () {
    expect(contrastRatio('#78716C', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('text-primary on surface-page meets WCAG AA body', function () {
    expect(contrastRatio('#1C1917', '#FAFAF9'))->toBeGreaterThanOrEqual(4.5);
});

test('text-on-dark on surface-sidebar meets WCAG AA body', function () {
    expect(contrastRatio('#FAFAF9', '#330000'))->toBeGreaterThanOrEqual(4.5);
});

test('text-on-dark-dim on surface-sidebar meets WCAG AA body', function () {
    expect(contrastRatio('#D6D3D1', '#330000'))->toBeGreaterThanOrEqual(4.5);
});

test('white on brand-500 meets WCAG AA body', function () {
    expect(contrastRatio('#FFFFFF', '#520000'))->toBeGreaterThanOrEqual(4.5);
});

test('white on brand-600 meets WCAG AA body', function () {
    expect(contrastRatio('#FFFFFF', '#440000'))->toBeGreaterThanOrEqual(4.5);
});

test('white on brand-700 meets WCAG AA body', function () {
    expect(contrastRatio('#FFFFFF', '#330000'))->toBeGreaterThanOrEqual(4.5);
});

// Semantic colors on white
test('success-500 on white meets WCAG AA body', function () {
    expect(contrastRatio('#15803D', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('success-700 on white meets WCAG AA body', function () {
    expect(contrastRatio('#166534', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('danger-500 on white meets WCAG AA body', function () {
    expect(contrastRatio('#DC2626', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('danger-700 on white meets WCAG AA body', function () {
    expect(contrastRatio('#B91C1C', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('info-500 on white meets WCAG AA body', function () {
    expect(contrastRatio('#2563EB', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('info-700 on white meets WCAG AA body', function () {
    expect(contrastRatio('#1D4ED8', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('warning-700 on white meets WCAG AA body', function () {
    expect(contrastRatio('#B45309', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

// Tier badges — large text rule (3:1)
test('white on tier-starter meets WCAG AA large text', function () {
    expect(contrastRatio('#FFFFFF', '#64748B'))->toBeGreaterThanOrEqual(3.0);
});

test('white on tier-pro meets WCAG AA large text', function () {
    expect(contrastRatio('#FFFFFF', '#520000'))->toBeGreaterThanOrEqual(3.0);
});

test('white on tier-enterprise meets WCAG AA large text', function () {
    expect(contrastRatio('#FFFFFF', '#D97706'))->toBeGreaterThanOrEqual(3.0);
});

// Link text
test('text-link on surface-card meets WCAG AA body', function () {
    expect(contrastRatio('#520000', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

test('text-link-hover on surface-card meets WCAG AA body', function () {
    expect(contrastRatio('#440000', '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});
