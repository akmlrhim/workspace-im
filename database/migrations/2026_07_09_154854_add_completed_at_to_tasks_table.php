<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('due_date');
        });

        // Backfill: attribute already-closed tasks to their last update time.
        // This is only a best-effort proxy for historical data; going forward
        // completed_at is set precisely when a task moves to a closed status.
        $closedStatusIds = DB::table('task_statuses')->where('type', 'closed')->pluck('id')->all();

        if (! empty($closedStatusIds)) {
            DB::table('tasks')
                ->whereIn('task_status_id', $closedStatusIds)
                ->whereNull('completed_at')
                ->update(['completed_at' => DB::raw('updated_at')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
