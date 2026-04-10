<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->foreignId('task_checklist_item_id')
                ->nullable()
                ->after('task_comment_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropForeign(['task_checklist_item_id']);
            $table->dropColumn('task_checklist_item_id');
        });
    }
};
