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
        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained('finance_accounts')->cascadeOnDelete();
            $table->foreignUuid('ledger_account_id')->constrained('finance_ledger_accounts')->cascadeOnDelete();
            $table->string('voucher_number'); // e.g. PV-2026-0001
            $table->string('expense_category'); // cogs, opex, capex, equity_injection, non_deductible
            $table->string('tax_classification')->default('deductible'); // deductible, cogs, capital_allowance, non_deductible, equity
            $table->string('vendor_name');
            $table->string('vendor_tin')->nullable(); // LHDN Tax Identification No (TIN)
            $table->string('vendor_brn')->nullable(); // SSM / MyKad
            $table->date('payment_date');
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00); // SST / Input Tax (0%, 6%, 8%)
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, cash, cheque, credit_card
            $table->string('reference_number')->nullable(); // Supplier Invoice / Receipt No
            $table->string('e_invoice_uuid')->nullable(); // LHDN e-Invoice UUID
            $table->string('e_invoice_status')->default('none'); // none, supplier_validated, self_billed
            $table->string('status')->default('posted'); // draft, posted, void
            $table->string('receipt_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'voucher_number']);
            $table->index(['org_id', 'payment_date']);
            $table->index(['org_id', 'expense_category']);
            $table->index(['org_id', 'tax_classification']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_expenses');
    }
};
