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
        Schema::table('hire_interviews', function (Blueprint $table) {
            $table->string('interviewer_name')->nullable()->after('interviewer_id');
            $table->string('location_or_link')->nullable()->after('mode');
            $table->text('notes')->nullable()->after('feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hire_interviews', function (Blueprint $table) {
            $table->dropColumn(['interviewer_name', 'location_or_link', 'notes']);
        });
    }
};
