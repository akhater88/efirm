<?php

use App\Enums\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);
});

test('widget card renders default state with slot content', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Test Widget"><p>Content</p></x-dashboard.widget-card>');

    $view->assertSee('Test Widget');
    $view->assertSee('Content');
});

test('widget card renders empty state', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Empty" state="empty" empty-message="Nothing here" />');

    $view->assertSee('Nothing here');
});

test('widget card renders loading state', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Loading" state="loading" />');

    $view->assertSee(__('common.loading'));
});

test('widget card renders error state', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Error" state="error" error-message="Something broke" />');

    $view->assertSee('Something broke');
});

test('widget card renders footer with view all and create links', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Footer" view-all-url="/test" create-url="/test/create"><p>Body</p></x-dashboard.widget-card>');

    $view->assertSee(__('common.view_all'));
    $view->assertSee(__('common.create'));
});

test('widget card empty state shows create CTA', function () {
    $view = $this->blade('<x-dashboard.widget-card title="Empty" state="empty" create-url="/test/create" create-label="Add item" />');

    $view->assertSee('Add item');
});

test('widget grid renders with slots', function () {
    $view = $this->blade('
        <x-dashboard.widget-grid>
            <x-slot:topLeft><div>TL</div></x-slot:topLeft>
            <x-slot:topRight><div>TR</div></x-slot:topRight>
            <x-slot:bottomLeft><div>BL</div></x-slot:bottomLeft>
            <x-slot:bottomRight><div>BR</div></x-slot:bottomRight>
        </x-dashboard.widget-grid>
    ');

    $view->assertSee('TL');
    $view->assertSee('TR');
    $view->assertSee('BL');
    $view->assertSee('BR');
});

test('widget grid renders feed slots', function () {
    $view = $this->blade('
        <x-dashboard.widget-grid>
            <x-slot:topLeft><div>TL</div></x-slot:topLeft>
            <x-slot:topRight><div>TR</div></x-slot:topRight>
            <x-slot:bottomLeft><div>BL</div></x-slot:bottomLeft>
            <x-slot:bottomRight><div>BR</div></x-slot:bottomRight>
            <x-slot:feedLeft><div>FL</div></x-slot:feedLeft>
            <x-slot:feedRight><div>FR</div></x-slot:feedRight>
        </x-dashboard.widget-grid>
    ');

    $view->assertSee('FL');
    $view->assertSee('FR');
});

test('dashboard renders with widget grid', function () {
    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee(__('dashboard.widget_matters'));
    $response->assertSee(__('dashboard.widget_tasks'));
});

test('dashboard and common lang files have key parity', function () {
    $enDash = require resource_path('lang/en/dashboard.php');
    $arDash = require resource_path('lang/ar/dashboard.php');
    expect(array_keys($enDash))->toBe(array_keys($arDash));

    $enCommon = require resource_path('lang/en/common.php');
    $arCommon = require resource_path('lang/ar/common.php');
    expect(array_keys($enCommon))->toBe(array_keys($arCommon));
});
