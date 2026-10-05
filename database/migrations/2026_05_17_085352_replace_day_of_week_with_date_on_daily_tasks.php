<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->date('date')->nullable()->after('is_active');
        });

        // Populate date from created_at for existing records
        DB::table('daily_tasks')->get()->each(function ($task) {
            DB::table('daily_tasks')->where('id', $task->id)->update([
                'date' => date('Y-m-d', strtotime($task->created_at)),
            ]);
        });

        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
            $table->dropColumn('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->default(1)->after('is_active');
            $table->dropColumn('date');
        });
    }
};
