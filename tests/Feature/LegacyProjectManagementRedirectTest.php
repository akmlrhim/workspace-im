<?php

use App\Models\User;

test('legacy prefix keeps the deep link and only drops the prefix', function (string $legacy, string $expected) {
    $this->get($legacy)->assertRedirect($expected);
})->with([
    'board' => ['/project-management/spaces/1/lists/2/board', '/spaces/1/lists/2/board'],
    'notes' => ['/project-management/spaces/1/lists/2/notes', '/spaces/1/lists/2/notes'],
    'list' => ['/project-management/spaces/9/lists/8', '/spaces/9/lists/8'],
    'my tasks' => ['/project-management/my-tasks', '/my-tasks'],
    'workload' => ['/project-management/workload', '/workload'],
    'general taskboard' => ['/project-management/general-taskboard', '/general-taskboard'],
]);

test('legacy paths that no longer exist fall back to the general taskboard', function (string $legacy) {
    $this->get($legacy)->assertRedirect('/general-taskboard');
})->with([
    'bare prefix' => '/project-management',
    'trailing slash' => '/project-management/',
    'retired space index' => '/project-management/spaces',
    'retired space page' => '/project-management/spaces/3',
    'nonsense' => '/project-management/does/not/exist',
]);

test('legacy redirect preserves the query string', function () {
    $this->get('/project-management/my-tasks?status=open&sort=due')
        ->assertRedirect('/my-tasks?status=open&sort=due');
});

test('legacy redirect lands an authenticated user on the real page', function () {
    $this->actingAs(User::factory()->create(['role' => 'member', 'position' => 'Kreatif']))
        ->get('/project-management/my-tasks')
        ->assertRedirect('/my-tasks');

    $this->get('/my-tasks')->assertOk();
});
