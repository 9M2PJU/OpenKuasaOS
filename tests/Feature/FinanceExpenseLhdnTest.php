<?php

namespace Tests\Feature;

use App\Models\FinanceAccount;
use App\Models\FinanceExpense;
use App\Models\FinanceJournal;
use App\Models\FinanceLedgerAccount;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Finance\BendaharaPostingService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceExpenseLhdnTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $user;
    protected FinanceAccount $bankAccount;
    protected BendaharaPostingService $postingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::create([
            'name' => 'Kasturi Tech Solutions Sdn Bhd',
            'slug' => 'kasturi-tech',
        ]);

        $this->user = User::create([
            'name' => 'Farhan Razak',
            'email' => 'farhan@kasturitech.my',
            'password' => bcrypt('password123'),
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);

        $this->bankAccount = FinanceAccount::create([
            'org_id' => $this->org->id,
            'name' => 'Maybank Korporat',
            'type' => 'bank',
            'account_number' => '564019283746',
            'balance' => 20000.00,
        ]);

        $this->postingService = app(BendaharaPostingService::class);
        $this->postingService->seedStandardChartOfAccounts($this->org->id);
    }

    public function test_can_record_cogs_expense_and_deduct_bank_balance(): void
    {
        $cogsLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '5000')->firstOrFail();

        $response = $this->actingAs($this->user)
            ->post(route('finance.expenses.store'), [
                'expense_category' => 'cogs',
                'tax_classification' => 'cogs',
                'vendor_name' => 'Pembekal Komponen Utama Sdn Bhd',
                'vendor_tin' => 'C9928172611',
                'vendor_brn' => '202301099881',
                'account_id' => $this->bankAccount->id,
                'ledger_account_id' => $cogsLedger->id,
                'payment_date' => now()->toDateString(),
                'subtotal' => 4500.00,
                'tax_rate' => 0,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'INV-SUP-001',
                'e_invoice_status' => 'supplier_validated',
                'description' => 'Belian stok barang niaga untuk jualan semula',
            ]);

        $response->assertRedirect(route('finance.expenses'));
        $response->assertSessionHas('success');

        // Check expense created and voucher generated
        $expense = FinanceExpense::first();
        $this->assertNotNull($expense);
        $this->assertStringStartsWith('PV-', $expense->voucher_number);
        $this->assertEquals(4500.00, (float) $expense->total_amount);
        $this->assertEquals('cogs', $expense->expense_category);
        $this->assertEquals('cogs', $expense->tax_classification);
        $this->assertEquals('posted', $expense->status);

        // Check bank balance deducted
        $this->bankAccount->refresh();
        $this->assertEquals(15500.00, (float) $this->bankAccount->balance);

        // Check balanced journal created
        $journal = FinanceJournal::where('reference_id', $expense->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('expense', $journal->reference_type);

        $lines = $journal->lines;
        $this->assertCount(2, $lines);
        $totalDebit = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');
        $this->assertEquals(4500.00, (float) $totalDebit);
        $this->assertEquals(4500.00, (float) $totalCredit);
    }

    public function test_can_record_expense_with_sst_input_tax(): void
    {
        $opexLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '6300')->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('finance.expenses.store'), [
                'expense_category' => 'opex',
                'tax_classification' => 'deductible',
                'vendor_name' => 'Agensi Pemasaran Digital Global',
                'vendor_tin' => 'C8819201992',
                'account_id' => $this->bankAccount->id,
                'ledger_account_id' => $opexLedger->id,
                'payment_date' => now()->toDateString(),
                'subtotal' => 2000.00,
                'tax_rate' => 8,
                'payment_method' => 'bank_transfer',
                'e_invoice_status' => 'none',
                'description' => 'Iklan digital Facebook & Google Ads',
            ]);

        $expense = FinanceExpense::latest()->first();
        $this->assertEquals(2000.00, (float) $expense->subtotal);
        $this->assertEquals(160.00, (float) $expense->tax_amount);
        $this->assertEquals(2160.00, (float) $expense->total_amount);

        // Bank balance reduced by total amount (including SST)
        $this->bankAccount->refresh();
        $this->assertEquals(17840.00, (float) $this->bankAccount->balance);

        // Check 3-line journal (Debit Expense, Debit SST Input, Credit Bank)
        $journal = FinanceJournal::where('reference_id', $expense->id)->first();
        $this->assertCount(3, $journal->lines);
        $this->assertEquals(2160.00, (float) $journal->lines->sum('debit'));
        $this->assertEquals(2160.00, (float) $journal->lines->sum('credit'));
    }

    public function test_can_record_equity_injection_and_increase_bank_balance(): void
    {
        $equityLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '3000')->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('finance.expenses.store'), [
                'expense_category' => 'equity_injection',
                'tax_classification' => 'equity',
                'vendor_name' => 'Farhan Razak (Pemilik Syarikat)',
                'vendor_brn' => '900101-10-5555',
                'account_id' => $this->bankAccount->id,
                'ledger_account_id' => $equityLedger->id,
                'payment_date' => now()->toDateString(),
                'subtotal' => 30000.00,
                'tax_rate' => 0,
                'payment_method' => 'bank_transfer',
                'e_invoice_status' => 'none',
                'description' => 'Suntikan modal permulaan pusingan modal perniagaan',
            ]);

        // Bank balance increased from 20,000 to 50,000
        $this->bankAccount->refresh();
        $this->assertEquals(50000.00, (float) $this->bankAccount->balance);

        $expense = FinanceExpense::first();
        $this->assertEquals('equity_injection', $expense->expense_category);

        // Journal: Debit Bank (1000), Credit Equity (3000)
        $journal = FinanceJournal::where('reference_id', $expense->id)->first();
        $this->assertEquals(30000.00, (float) $journal->lines->sum('debit'));
        $this->assertEquals(30000.00, (float) $journal->lines->sum('credit'));
    }

    public function test_can_record_capex_for_lhdn_capital_allowance(): void
    {
        $capexLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '1500')->firstOrFail();

        $this->actingAs($this->user)
            ->post(route('finance.expenses.store'), [
                'expense_category' => 'capex',
                'tax_classification' => 'capital_allowance',
                'vendor_name' => 'Dell Technologies Malaysia',
                'vendor_tin' => 'C1122334455',
                'account_id' => $this->bankAccount->id,
                'ledger_account_id' => $capexLedger->id,
                'payment_date' => now()->toDateString(),
                'subtotal' => 7500.00,
                'tax_rate' => 0,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'INV-DELL-99',
                'e_invoice_status' => 'supplier_validated',
                'description' => 'Pembelian komputer riba Dell Precision',
            ]);

        $expense = FinanceExpense::first();
        $this->assertEquals('capex', $expense->expense_category);
        $this->assertEquals('capital_allowance', $expense->tax_classification);

        $this->bankAccount->refresh();
        $this->assertEquals(12500.00, (float) $this->bankAccount->balance);
    }

    public function test_can_void_expense_and_restore_bank_balance(): void
    {
        $opexLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '6100')->firstOrFail();

        $exp = FinanceExpense::create([
            'org_id' => $this->org->id,
            'account_id' => $this->bankAccount->id,
            'ledger_account_id' => $opexLedger->id,
            'voucher_number' => 'PV-2026-0001',
            'expense_category' => 'opex',
            'tax_classification' => 'deductible',
            'vendor_name' => 'Tuan Rumah Pejabat',
            'payment_date' => now()->toDateString(),
            'subtotal' => 2000.00,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => 2000.00,
            'status' => 'draft',
        ]);

        $this->postingService->postExpense($exp);
        $this->bankAccount->refresh();
        $this->assertEquals(18000.00, (float) $this->bankAccount->balance);

        // Void the expense
        $response = $this->actingAs($this->user)
            ->post(route('finance.expenses.void', $exp->id), [
                'reason' => 'Bayaran dibuat dua kali secara tidak sengaja',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Bank balance restored to 20,000.00
        $this->bankAccount->refresh();
        $this->assertEquals(20000.00, (float) $this->bankAccount->balance);

        $exp->refresh();
        $this->assertEquals('void', $exp->status);
    }

    public function test_web_screens_render_expenses_correctly(): void
    {
        $opexLedger = FinanceLedgerAccount::where('org_id', $this->org->id)->where('code', '6100')->firstOrFail();

        $exp = FinanceExpense::create([
            'org_id' => $this->org->id,
            'account_id' => $this->bankAccount->id,
            'ledger_account_id' => $opexLedger->id,
            'voucher_number' => 'PV-2026-0001',
            'expense_category' => 'opex',
            'tax_classification' => 'deductible',
            'vendor_name' => 'Telekom Malaysia Berhad',
            'vendor_tin' => 'C9911223344',
            'payment_date' => now()->toDateString(),
            'subtotal' => 350.00,
            'tax_rate' => 6,
            'tax_amount' => 21.00,
            'total_amount' => 371.00,
            'status' => 'draft',
        ]);
        $this->postingService->postExpense($exp);

        // Test Finance Index includes expenses and metrics
        $this->actingAs($this->user)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertSee('Perbelanjaan', false)
            ->assertSee('PV-2026-0001')
            ->assertSee('Telekom Malaysia Berhad');

        // Test Expenses List
        $this->actingAs($this->user)
            ->get(route('finance.expenses'))
            ->assertOk()
            ->assertSee('Perbelanjaan', false)
            ->assertSee('PV-2026-0001')
            ->assertSee('Telekom Malaysia Berhad');

        // Test Create Expense form
        $this->actingAs($this->user)
            ->get(route('finance.expenses.create'))
            ->assertOk()
            ->assertSee('Rekod Perbelanjaan', false)
            ->assertSee('Klasifikasi Cukai LHDN');

        // Test Show Payment Voucher (official print view)
        $this->actingAs($this->user)
            ->get(route('finance.expenses.show', $exp->id))
            ->assertOk()
            ->assertSee('Baucar Pembayaran')
            ->assertSee('PV-2026-0001')
            ->assertSee('C9911223344')
            ->assertSee('Disediakan Oleh')
            ->assertSee('Disahkan Oleh');
    }
}
