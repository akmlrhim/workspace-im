<?php

use App\Models\User;

test('unauthenticated users are redirected to login', function () {
    $response = $this->get(route('project-management.general-taskboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with access can visit the general taskboard', function () {
    $user = User::factory()->create(['role' => 'member', 'position' => 'Developer']);
    $this->actingAs($user);

    $response = $this->get(route('project-management.general-taskboard'));
    $response->assertOk();
});
