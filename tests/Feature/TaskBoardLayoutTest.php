<?php

use App\Livewire\TaskBoard;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function makeBoardLayoutContext(): array
{
    $user = User::factory()->create(['role' => 'administrator', 'name' => 'Andi Pratama']);
    test()->actingAs($user);

    $workspace = Workspace::create(['name' => 'Workspace Utama', 'owner_id' => $user->id]);
    WorkspaceMember::create(['workspace_id' => $workspace->id, 'user_id' => $user->id, 'role' => 'owner']);

    $space = $workspace->spaces()->create(['name' => 'Ruang Desain', 'position' => 0, 'color' => '#6366f1', 'icon' => 'folder']);
    $list = $space->lists()->create(['name' => 'Daftar Backlog', 'position' => 0]);
    $list->members()->attach($user->id);

    $todo = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    $doing = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Dikerjakan', 'position' => 1, 'type' => 'active', 'color' => '#3b82f6']);

    Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $todo->id,
        'title' => 'Rancang ulang kartu',
        'position' => 0,
        'created_by' => $user->id,
    ]);

    return compact('user', 'space', 'list', 'todo', 'doing');
}

test('column carries no colored border, only the dot beside the status name', function () {
    $ctx = makeBoardLayoutContext();

    $html = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])->html();

    expect($html)
        ->not->toContain('border-top-color')
        ->and($html)->not->toContain('border-t-4')

        ->and($html)->toContain('rounded-full" style="background-color: '.$ctx['todo']->color);
});

test('each column is its own scroll container so the page never scrolls', function () {
    $ctx = makeBoardLayoutContext();

    $html = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])->html();

    expect($html)
        ->toContain('kanban-board flex min-h-[20rem] flex-1')
        ->and($html)->toContain('kanban-column custom-scrollbar')
        ->and($html)->toContain('overflow-y-auto overscroll-y-contain');
});

test('an empty column shows a drop hint and a filled one does not', function () {
    $ctx = makeBoardLayoutContext();

    $html = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])->html();

    preg_match_all('/data-empty-hint class="([^"]*)"/', $html, $matches);

    expect($matches[1])->toHaveCount(2)
        ->and($matches[1][0])->toContain('hidden')
        ->and($matches[1][1])->not->toContain('hidden')
        ->and($html)->toContain('Belum ada tugas')
        ->and($html)->toContain('Rancang ulang kartu');
});

test('the drag handler hides the hint itself, because moveTask skips the re-render', function () {
    $component = file_get_contents(app_path('Livewire/TaskBoard.php'));
    $script = file_get_contents(resource_path('views/livewire/partials/board/kanban-script.blade.php'));

    expect($component)->toContain('$this->skipRender();')
        ->and($script)->toContain('this._syncColumnChrome(evt.from, evt.to);')
        ->and($script)->toContain("emptyHint.classList.toggle('hidden', count > 0);");
});

test('cards keep their natural height instead of being squashed by the column', function () {
    $ctx = makeBoardLayoutContext();

    foreach (range(1, 12) as $i) {
        Task::create([
            'task_list_id' => $ctx['list']->id,
            'task_status_id' => $ctx['todo']->id,
            'title' => "Tugas ke-{$i}",
            'position' => $i,
            'created_by' => $ctx['user']->id,
        ]);
    }

    $html = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])->html();

    expect(substr_count($html, 'task-card group/card relative shrink-0'))->toBe(13)
        ->and($html)->toContain('Tugas ke-12');
})->group('layout');

test('horizontal panning is wired up and cannot be fought by smooth scroll or snap', function () {
    $ctx = makeBoardLayoutContext();

    $html = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])->html();
    $script = file_get_contents(resource_path('views/livewire/partials/board/kanban-script.blade.php'));
    $css = file_get_contents(resource_path('css/app.css'));

    expect($html)
        ->toContain('@wheel="onWheel"')
        ->and($html)->toContain('@lostpointercapture="stopDrag"')
        ->and($html)->not->toContain('@pointerleave="stopDrag"')
        ->and($html)->toContain('overscroll-y-contain')
        ->and($html)->not->toContain('overscroll-contain px-2');

    expect($script)
        ->toContain("this.\$el.classList.add('is-panning');")
        ->toContain('setPointerCapture')
        ->toContain('e.button === 1');

    expect($css)->toContain('.kanban-board.is-panning {');
})->group('layout');

test('scroll position is preserved across a re-render', function () {
    $script = file_get_contents(resource_path('views/livewire/partials/board/kanban-script.blade.php'));

    expect($script)
        ->toContain("addEventListener('scroll', this._onAnyScroll, { capture: true, passive: true })")
        ->toContain('_rememberScroll()')
        ->toContain('_restoreScroll()')
        ->toContain('this._scrollTops[id] = col.scrollTop;');

    expect(substr_count($script, 'this._restoreScroll();'))->toBeGreaterThanOrEqual(2);
})->group('layout');

test('a lost pointerup can never leave the board frozen', function () {
    $script = file_get_contents(resource_path('views/livewire/partials/board/kanban-script.blade.php'));
    $css = file_get_contents(resource_path('css/app.css'));

    // .is-panning puts pointer-events: none on every child; if the class ever
    // stuck, the whole board would go dead. Window-level listeners are the escape.
    expect($css)->toContain('pointer-events: none;')
        ->and($script)->toContain("window.addEventListener('pointerup', this._onWindowPointerUp);")
        ->and($script)->toContain("window.addEventListener('blur', this._onWindowPointerUp);")
        ->and($script)->toContain("window.removeEventListener('pointerup', this._onWindowPointerUp);");
})->group('layout');
