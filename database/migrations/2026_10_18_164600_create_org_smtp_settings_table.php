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
        Schema::create('org_smtp_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->unique()->constrained('orgs')->cascadeOnDelete();
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->default(587);
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable(); // Encrypted at rest via Eloquent 'encrypted' cast
            $table->string('mail_encryption')->nullable()->default('tls'); // tls, ssl, or none
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_smtp_settings');
    }
};
