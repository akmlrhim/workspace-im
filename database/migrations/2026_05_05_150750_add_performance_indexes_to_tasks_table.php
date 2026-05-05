<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Covers kanban column query: WHERE task_status_id = ? AND parent_id IS NULL ORDER BY position
            $table->index(['task_status_id', 'parent_id', 'position'], 'tasks_status_parent_position');

            // Covers list view + deadline reminder: WHERE task_list_id = ? AND parent_id IS NULL ORDER BY position
            $table->index(['task_list_id', 'parent_id', 'position'], 'tasks_list_parent_position');

            // Covers deadline reminder command: WHERE due_date IN (today, tomorrow)
            $table->index('due_date', 'tasks_due_date');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_parent_position');
            $table->dropIndex('tasks_list_parent_position');
            $table->dropIndex('tasks_due_date');
        });
    }
};
