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
        Schema::create('finance_quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->uuid('customer_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('quotation_number');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, converted
            $table->date('issue_date');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('sst_rate', 5, 2)->default(0.00);
            $table->decimal('sst_amount', 14, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->uuid('converted_invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'quotation_number']);
            $table->index(['org_id', 'status']);
        });

        Schema::create('finance_quotation_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('quotation_id')->constrained('finance_quotations')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 14, 2)->default(0.00);
            $table->decimal('total', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::table('finance_invoices', function (Blueprint $table) {
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->uuid('quotation_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_invoices', function (Blueprint $table) {
            $table->dropColumn(['customer_phone', 'quotation_id']);
        });
        Schema::dropIfExists('finance_quotation_lines');
        Schema::dropIfExists('finance_quotations');
    }
};
