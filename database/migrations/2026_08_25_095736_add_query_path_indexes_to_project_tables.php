<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['assigned_to', 'task_list_id'], 'tasks_assigned_to_list');

            $table->index(['due_date', 'parent_id'], 'tasks_due_date_parent');
        });

        if (Schema::hasIndex('tasks', 'tasks_due_date')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_due_date');
            });
        }

        Schema::table('task_activities', function (Blueprint $table) {
            $table->index(['task_id', 'created_at'], 'task_activities_task_created');
        });

        Schema::table('task_comments', function (Blueprint $table) {
            $table->index(['task_id', 'parent_id', 'created_at'], 'task_comments_task_parent_created');
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            $table->index(['task_id', 'task_checklist_item_id', 'created_at'], 'task_attachments_task_item_created');
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            $table->index(['task_id', 'user_id', 'stopped_at'], 'time_trackings_task_user_stopped');
        });

        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->index(['task_list_id', 'is_active', 'date'], 'daily_tasks_list_active_date');
        });

        Schema::table('daily_task_logs', function (Blueprint $table) {
            $table->index(['daily_task_id', 'date'], 'daily_task_logs_task_date');
        });
    }

    public function down(): void
    {
        $this->restoreIndex('tasks', 'assigned_to', 'tasks_assigned_to_foreign');
        $this->restoreIndex('tasks', 'due_date', 'tasks_due_date');
        $this->restoreIndex('task_activities', 'task_id', 'task_activities_task_id_foreign');
        $this->restoreIndex('task_comments', 'task_id', 'task_comments_task_id_foreign');
        $this->restoreIndex('task_attachments', 'task_id', 'task_attachments_task_id_foreign');
        $this->restoreIndex('time_trackings', 'task_id', 'time_trackings_task_id_foreign');
        $this->restoreIndex('daily_tasks', 'task_list_id', 'daily_tasks_task_list_id_foreign');

        $composites = [
            'tasks' => ['tasks_assigned_to_list', 'tasks_due_date_parent'],
            'task_activities' => ['task_activities_task_created'],
            'task_comments' => ['task_comments_task_parent_created'],
            'task_attachments' => ['task_attachments_task_item_created'],
            'time_trackings' => ['time_trackings_task_user_stopped'],
            'daily_tasks' => ['daily_tasks_list_active_date'],
            'daily_task_logs' => ['daily_task_logs_task_date'],
        ];

        foreach ($composites as $tableName => $indexes) {
            foreach ($indexes as $index) {
                if (Schema::hasIndex($tableName, $index)) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->dropIndex($index));
                }
            }
        }
    }

    private function restoreIndex(string $tableName, string $column, string $indexName): void
    {
        if (! Schema::hasIndex($tableName, $indexName)) {
            Schema::table($tableName, fn (Blueprint $table) => $table->index($column, $indexName));
        }
    }
};
