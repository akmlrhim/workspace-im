<?php

use Carbon\Carbon;
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
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->default(1)->after('is_active');
        });

        DB::table('daily_tasks')->get()->each(function ($task) {
            DB::table('daily_tasks')->where('id', $task->id)->update([
                'day_of_week' => Carbon::parse($task->created_at)->isoWeekday(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }
};
