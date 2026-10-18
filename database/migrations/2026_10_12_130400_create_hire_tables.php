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
        Schema::create('hire_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // published, draft, closed
            $table->timestamps();
        });

        Schema::create('hire_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hire_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('job_id')->constrained('hire_jobs')->cascadeOnDelete();
            $table->foreignUuid('candidate_id')->nullable()->constrained('hire_candidates')->nullOnDelete();
            $table->string('candidate_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('cv_file_url')->nullable();
            $table->string('stage')->default('applied'); // applied, screening, interview, offered, hired, rejected
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('hire_interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('application_id')->constrained('hire_applications')->cascadeOnDelete();
            $table->foreignUuid('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at');
            $table->string('mode')->default('online'); // online, in_person
            $table->string('meeting_link')->nullable();
            $table->text('feedback')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_interviews');
        Schema::dropIfExists('hire_applications');
        Schema::dropIfExists('hire_candidates');
        Schema::dropIfExists('hire_jobs');
    }
};
