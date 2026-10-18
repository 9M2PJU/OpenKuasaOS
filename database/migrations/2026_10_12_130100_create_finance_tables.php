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
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('bank'); // bank, cash, credit_card
            $table->string('account_number')->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->decimal('balance', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('finance_ledger_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->uuid('parent_id')->nullable();
            $table->decimal('balance', 14, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['org_id', 'code']);
        });

        Schema::table('finance_ledger_accounts', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('finance_ledger_accounts')->nullOnDelete();
        });

        Schema::create('finance_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->uuid('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('status')->default('draft'); // draft, posted, paid, void
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('sst_rate', 5, 2)->default(0.00);
            $table->decimal('sst_amount', 14, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->decimal('amount_paid', 14, 2)->default(0.00);
            $table->string('e_invoice_uuid')->nullable();
            $table->string('e_invoice_status')->default('none'); // none, pending, validated, rejected
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'invoice_number']);
        });

        Schema::create('finance_invoice_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('finance_invoices')->cascadeOnDelete();
            $table->uuid('product_id')->nullable();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 14, 2)->default(0.00);
            $table->string('tax_code')->nullable();
            $table->decimal('total', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('finance_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->string('status')->default('posted'); // draft, posted, void
            $table->timestamps();
        });

        Schema::create('finance_journal_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_id')->constrained('finance_journals')->cascadeOnDelete();
            $table->foreignUuid('ledger_account_id')->constrained('finance_ledger_accounts')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0.00);
            $table->decimal('credit', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('finance_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('finance_invoices')->nullOnDelete();
            $table->foreignUuid('account_id')->constrained('finance_accounts')->cascadeOnDelete();
            $table->string('receipt_number');
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('bank_transfer');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_receipts');
        Schema::dropIfExists('finance_journal_lines');
        Schema::dropIfExists('finance_journals');
        Schema::dropIfExists('finance_invoice_lines');
        Schema::dropIfExists('finance_invoices');
        Schema::dropIfExists('finance_ledger_accounts');
        Schema::dropIfExists('finance_accounts');
    }
};
