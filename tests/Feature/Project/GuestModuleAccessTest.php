<?php

use App\Models\User;

test('guest user is redirected to dashboard when accessing project module', function () {
    $guest = User::factory()->create(['role' => 'guest', 'position' => null]);

    $this->actingAs($guest)
        ->get(route('project-management.general-taskboard'))
        ->assertRedirect(route('dashboard'));
});

test('guest user is redirected from every project management sub-route', function () {
    $guest = User::factory()->create(['role' => 'guest', 'position' => null]);
    $this->actingAs($guest);

    $this->get(route('project-management.index'))->assertRedirect(route('dashboard'));
    $this->get(route('project-management.my-tasks'))->assertRedirect(route('dashboard'));
    $this->get(route('project-management.workload'))->assertRedirect(route('dashboard'));
});

test('member user with a position can access project module', function () {
    $member = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);

    $this->actingAs($member)
        ->get(route('project-management.general-taskboard'))
        ->assertOk();
});
