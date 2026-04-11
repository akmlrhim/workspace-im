<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Set first user to super_user, all others null/old roles → member
        $firstUserId = DB::table('users')->min('id');

        DB::table('users')
            ->where('id', $firstUserId)
            ->update(['role' => 'super_user']);

        DB::table('users')
            ->where('id', '!=', $firstUserId)
            ->where(function ($q) {
                $q->whereNull('role')->orWhereIn('role', ['admin', 'user']);
            })
            ->update(['role' => 'member']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->default(null)->change();
        });
    }
};
