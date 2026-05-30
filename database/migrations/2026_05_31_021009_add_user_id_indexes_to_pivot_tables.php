<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Speeds up "find all tasks assigned to user X" queries used by accessibleBy scopes
        Schema::table('task_user', function (Blueprint $table) {
            $table->index('user_id', 'task_user_user_id');
        });

        // Speeds up "find all lists where user is member" queries used by accessibleBy scopes
        Schema::table('task_list_user', function (Blueprint $table) {
            $table->index('user_id', 'task_list_user_user_id');
        });

        // Speeds up workspace membership lookups used on every sidebar render
        Schema::table('workspace_members', function (Blueprint $table) {
            $table->index('user_id', 'workspace_members_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropIndex('task_user_user_id');
        });

        Schema::table('task_list_user', function (Blueprint $table) {
            $table->dropIndex('task_list_user_user_id');
        });

        Schema::table('workspace_members', function (Blueprint $table) {
            $table->dropIndex('workspace_members_user_id');
        });
    }
};
