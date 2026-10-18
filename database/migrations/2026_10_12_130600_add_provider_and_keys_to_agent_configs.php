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
        Schema::table('agent_configs', function (Blueprint $table) {
            $table->string('provider')->default('ollama')->after('agent_name');
            $table->text('gemini_api_key')->nullable()->after('provider');
            $table->string('gemini_model')->default('gemini-2.5-flash')->after('gemini_api_key');
            $table->text('ollama_url')->nullable()->after('gemini_model');
            $table->string('ollama_model')->default('gemma4:e2b')->after('ollama_url');
            $table->boolean('is_active')->default(true)->after('temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_configs', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'gemini_api_key',
                'gemini_model',
                'ollama_url',
                'ollama_model',
                'is_active',
            ]);
        });
    }
};
