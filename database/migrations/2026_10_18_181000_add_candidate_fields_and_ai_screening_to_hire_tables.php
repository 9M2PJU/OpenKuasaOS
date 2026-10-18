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
        Schema::table('hire_jobs', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->text('requirements')->nullable()->after('description');
            $table->decimal('salary_min', 12, 2)->nullable()->after('requirements');
            $table->decimal('salary_max', 12, 2)->nullable()->after('salary_min');
        });

        Schema::table('hire_candidates', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('phone');
            $table->text('address')->nullable()->after('birth_date');
        });

        Schema::table('hire_applications', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('phone');
            $table->text('address')->nullable()->after('birth_date');
            $table->text('cover_letter')->nullable()->after('cv_file_url');
            $table->integer('ai_score')->nullable()->after('status');
            $table->string('ai_status')->nullable()->after('ai_score'); // layak, pertimbangan, tidak_layak
            $table->json('ai_analysis')->nullable()->after('ai_status');
            $table->timestamp('ai_reviewed_at')->nullable()->after('ai_analysis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hire_applications', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'address',
                'cover_letter',
                'ai_score',
                'ai_status',
                'ai_analysis',
                'ai_reviewed_at',
            ]);
        });

        Schema::table('hire_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'address',
            ]);
        });

        Schema::table('hire_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'requirements',
                'salary_min',
                'salary_max',
            ]);
        });
    }
};
