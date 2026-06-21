<?php

it('returns 200 with healthy status', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
    $response->assertJsonPath('status', 'healthy');
});

it('returns correct JSON structure', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
    $response->assertJsonStructure([
        'status',
        'checks' => [
            'database',
            'redis',
        ],
        'timestamp',
    ]);
});

it('is accessible without authentication', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
});
