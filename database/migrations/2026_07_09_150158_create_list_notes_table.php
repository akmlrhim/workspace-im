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
		Schema::create('list_notes', function (Blueprint $table) {
			$table->id();
			$table->char('uuid', 36)->unique();
			$table->foreignId('task_list_id')->constrained('task_lists')->cascadeOnDelete();
			$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
			$table->string('title');
			$table->text('content')->nullable();
			$table->unsignedSmallInteger('position')->default(0);
			$table->timestamps();

			$table->index(['task_list_id', 'position']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('list_notes');
	}
};
