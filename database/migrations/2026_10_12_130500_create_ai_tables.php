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
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->default('New Conversation');
            $table->string('context_module')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->string('role'); // user, assistant, system
            $table->text('content');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('agent_name');
            $table->string('model')->default('anthropic/claude-3.5-sonnet');
            $table->text('system_prompt')->nullable();
            $table->jsonb('tools_enabled')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->timestamps();
        });

        Schema::create('agent_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('agent_config_id')->nullable()->constrained('agent_configs')->nullOnDelete();
            $table->foreignUuid('thread_id')->nullable()->constrained('chat_threads')->nullOnDelete();
            $table->text('prompt');
            $table->text('output')->nullable();
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('tokens_used')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
        Schema::dropIfExists('agent_configs');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_threads');
    }
};
