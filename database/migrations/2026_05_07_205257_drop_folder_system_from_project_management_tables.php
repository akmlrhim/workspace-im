<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('task_lists', 'folder_id')) {
            Schema::table('task_lists', function (Blueprint $table) {
                $table->dropConstrainedForeignId('folder_id');
            });
        }

        Schema::dropIfExists('folders');
    }

    public function down(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        if (! Schema::hasColumn('task_lists', 'folder_id')) {
            Schema::table('task_lists', function (Blueprint $table) {
                $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }
};
