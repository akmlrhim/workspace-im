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
		Schema::create('list_note_attachments', function (Blueprint $table) {
			$table->id();
			$table->char('uuid', 36)->unique();
			$table->foreignId('list_note_id')->constrained('list_notes')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('filename');
			$table->string('path', 2048);
			$table->string('mime_type')->default('application/octet-stream');
			$table->unsignedBigInteger('size')->default(0);
			$table->boolean('is_link')->default(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('list_note_attachments');
	}
};
