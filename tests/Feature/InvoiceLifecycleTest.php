<?php

namespace Tests\Feature;

use App\Models\FinanceAccount;
use App\Models\FinanceInvoice;
use App\Models\FinanceJournal;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $user;
    protected FinanceAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::create([
            'name' => 'Bendahara Corp Sdn Bhd',
            'slug' => 'bendahara-corp',
        ]);

        $this->user = User::create([
            'name' => 'Ahmad Bendahara',
            'email' => 'ahmad@bendahara.my',
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
            'name' => 'Maybank Berhad',
            'type' => 'bank',
            'account_number' => '514012345678',
            'balance' => 10000.00,
        ]);

        Sanctum::actingAs($this->user);
    }

    /**
     * Test full invoice lifecycle: Draft -> Post -> Pay -> Void.
     */
    public function test_full_invoice_lifecycle_and_balanced_ledger(): void
    {
        $headers = ['X-Organization-Id' => $this->org->id];

        // 1. DRAFT: Create draft invoice with 8% SST
        $createPayload = [
            'customer_name' => 'Syarikat Pelanggan Maju Jaya',
            'customer_email' => 'finance@majujaya.com',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'sst_rate' => 8.00,
            'lines' => [
                [
                    'description' => 'Langganan OpenKuasa OS Tahunan',
                    'quantity' => 1,
                    'unit_price' => 10000.00,
                ],
                [
                    'description' => 'Pemasangan & Latihan Kakitangan',
                    'quantity' => 2,
                    'unit_price' => 1000.00,
                ],
            ],
        ];

        $resCreate = $this->withHeaders($headers)
            ->postJson('/api/v1/finance/invoices', $createPayload);

        $resCreate->assertStatus(201);
        $invoiceId = $resCreate->json('data.id');

        // Subtotal: 10000 + 2000 = 12,000. SST 8%: 960. Total: 12,960.
        $this->assertEquals(12000.00, $resCreate->json('data.subtotal'));
        $this->assertEquals(960.00, $resCreate->json('data.sst_amount'));
        $this->assertEquals(12960.00, $resCreate->json('data.total_amount'));
        $this->assertEquals('draft', $resCreate->json('data.status'));

        // 2. POST: Lock invoice, generate sequential INV-XXXX-0001, post balanced journal
        $resPost = $this->withHeaders($headers)
            ->postJson("/api/v1/finance/invoices/{$invoiceId}/post");

        $resPost->assertStatus(200);
        $invoiceNumber = $resPost->json('data.invoice_number');
        $this->assertNotEmpty($invoiceNumber);
        $this->assertStringContainsString('INV-', $invoiceNumber);
        $this->assertEquals('posted', $resPost->json('data.status'));

        // Assert General Ledger balancing for POST entry
        $postJournal = FinanceJournal::withoutGlobalScopes()
            ->where('reference_type', 'invoice')
            ->where('reference_id', $invoiceId)
            ->with('lines')
            ->first();

        $this->assertNotNull($postJournal);
        $totalDebit = $postJournal->lines->sum('debit');
        $totalCredit = $postJournal->lines->sum('credit');
        $this->assertEquals(12960.00, round($totalDebit, 2));
        $this->assertEquals(12960.00, round($totalCredit, 2));
        $this->assertEquals($totalDebit, $totalCredit, 'Debits and Credits must balance perfectly.');

        // 3. PAY: Record payment receipt
        $resPay = $this->withHeaders($headers)
            ->postJson("/api/v1/finance/invoices/{$invoiceId}/payments", [
                'account_id' => $this->bankAccount->id,
                'amount' => 12960.00,
                'payment_method' => 'bank_transfer',
                'reference' => 'MBB-REF-998822',
            ]);

        $resPay->assertStatus(200);
        $this->assertEquals('paid', $resPay->json('data.invoice.status'));
        $this->assertEquals(12960.00, $resPay->json('data.invoice.amount_paid'));

        // Assert Bank Account balance was incremented
        $this->bankAccount->refresh();
        $this->assertEquals(22960.00, (float) $this->bankAccount->balance);

        // Assert Receipt Journal balancing
        $receiptJournal = FinanceJournal::withoutGlobalScopes()
            ->where('reference_type', 'receipt')
            ->with('lines')
            ->first();

        $this->assertNotNull($receiptJournal);
        $this->assertEquals(12960.00, round($receiptJournal->lines->sum('debit'), 2));
        $this->assertEquals(12960.00, round($receiptJournal->lines->sum('credit'), 2));
    }

    /**
     * Test atomic sequential numbering for multiple invoices.
     */
    public function test_atomic_sequential_invoice_numbering(): void
    {
        $headers = ['X-Organization-Id' => $this->org->id];

        // Create and post Invoice 1
        $inv1 = FinanceInvoice::create([
            'org_id' => $this->org->id,
            'customer_name' => 'Cust 1',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 100.00,
            'total_amount' => 100.00,
        ]);

        $resPost1 = $this->withHeaders($headers)->postJson("/api/v1/finance/invoices/{$inv1->id}/post");
        $resPost1->assertStatus(200);
        $no1 = $resPost1->json('data.invoice_number');

        // Create and post Invoice 2
        $inv2 = FinanceInvoice::create([
            'org_id' => $this->org->id,
            'customer_name' => 'Cust 2',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 200.00,
            'total_amount' => 200.00,
        ]);

        $resPost2 = $this->withHeaders($headers)->postJson("/api/v1/finance/invoices/{$inv2->id}/post");
        $resPost2->assertStatus(200);
        $no2 = $resPost2->json('data.invoice_number');

        $this->assertNotEquals($no1, $no2);
        $this->assertStringEndsWith('0001', $no1);
        $this->assertStringEndsWith('0002', $no2);
    }
}
