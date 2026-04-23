<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->boolean('is_link')->default(false)->after('size');
            $table->text('path')->change();
        });
    }

    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropColumn('is_link');
            $table->string('path')->change();
        });
    }
};
