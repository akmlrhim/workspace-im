<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\DB;

trait BatchesPositionUpdates
{
    /**
     * @param  'tasks'|'task_statuses'  $table  literal table name, never user input
     * @param  array<int, int>  $orderedIds
     * @param  array<string, int>  $scope  extra `column = value` guards on the UPDATE
     */
    private function applyPositionOrder(string $table, array $orderedIds, array $scope = []): void
    {
        if (empty($orderedIds)) {
            return;
        }

        $cases = [];
        $bindings = [];

        foreach ($orderedIds as $position => $id) {
            $cases[] = 'WHEN id = ? THEN ?';
            $bindings[] = $id;
            $bindings[] = $position;
        }

        $conditions = [];

        foreach ($scope as $column => $value) {
            $conditions[] = "{$column} = ?";
            $bindings[] = $value;
        }

        $conditions[] = 'id IN ('.implode(',', array_fill(0, count($orderedIds), '?')).')';
        $bindings = array_merge($bindings, array_values($orderedIds));

        DB::update(
            "UPDATE {$table} SET position = CASE ".implode(' ', $cases).' END WHERE '.implode(' AND ', $conditions),
            $bindings
        );
    }
}
