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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superadmin')->default(false)->after('locale');
            $table->boolean('is_demo')->default(false)->after('is_superadmin');
            $table->string('status', 20)->default('active')->after('is_demo');

            $table->index('is_superadmin', 'idx_users_is_superadmin');
            $table->index('is_demo', 'idx_users_is_demo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_is_superadmin');
            $table->dropIndex('idx_users_is_demo');
            $table->dropColumn(['is_superadmin', 'is_demo', 'status']);
        });
    }
};
