<?php

namespace Tests\Feature;

use App\Models\FinanceAccount;
use App\Models\FinanceInvoice;
use App\Models\FinanceQuotation;
use App\Models\FinanceQuotationLine;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Finance\BendaharaPostingService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationLifecycleTest extends TestCase
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
            'name' => 'Bendahara Trading Sdn Bhd',
            'slug' => 'bendahara-trading',
        ]);

        $this->user = User::create([
            'name' => 'Haji Zainal Bendahara',
            'email' => 'zainal@bendaharatrading.my',
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
            'name' => 'CIMB Islamic Biz',
            'type' => 'bank',
            'account_number' => '860012345678',
            'balance' => 15000.00,
        ]);

        $this->postingService = app(BendaharaPostingService::class);
        $this->postingService->seedStandardChartOfAccounts($this->org->id);
    }

    /**
     * Test quotation routes render properly.
     */
    public function test_can_render_quotations_index_and_create_pages(): void
    {
        $response = $this->actingAs($this->user)->get(route('finance.quotations'));
        $response->assertOk();
        $response->assertSee('Sebutharga');

        $responseCreate = $this->actingAs($this->user)->get(route('finance.quotations.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Cipta Sebutharga Baru');
    }

    /**
     * Test creating a quotation with items, SST, and customer phone.
     */
    public function test_can_create_quotation_with_multiple_items_and_sst(): void
    {
        $payload = [
            'customer_name' => 'Mega Bina Holdings Sdn Bhd',
            'customer_email' => 'perolehan@megabina.com',
            'customer_phone' => '012-345 6789',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'sst_rate' => 8,
            'notes' => 'Sebutharga termasuk jaminan 1 tahun.',
            'terms' => 'Pembayaran 30 hari dari tarikh invois.',
            'lines' => [
                [
                    'description' => 'Pemasangan Server Cloud OpenKuasa',
                    'quantity' => 2,
                    'unit_price' => 1500.00,
                ],
                [
                    'description' => 'Latihan Sistem & Konfigurasi LHDN',
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('finance.quotations.store'), $payload);

        $quotation = FinanceQuotation::where('org_id', $this->org->id)->first();
        $this->assertNotNull($quotation);
        $this->assertStringStartsWith('QT-', $quotation->quotation_number);
        $this->assertEquals('Mega Bina Holdings Sdn Bhd', $quotation->customer_name);
        $this->assertEquals('012-345 6789', $quotation->customer_phone);
        $this->assertEquals('draft', $quotation->status);

        // Subtotal = (2 * 1500) + (1 * 1000) = 4000.00
        // SST 8% = 320.00
        // Total = 4320.00
        $this->assertEquals(4000.00, (float) $quotation->subtotal);
        $this->assertEquals(8.00, (float) $quotation->sst_rate);
        $this->assertEquals(320.00, (float) $quotation->sst_amount);
        $this->assertEquals(4320.00, (float) $quotation->total_amount);

        $this->assertCount(2, $quotation->lines);

        $response->assertRedirect(route('finance.quotations.show', $quotation->id));
        $response->assertSessionHas('success');
    }

    /**
     * Test quotation detail view contains WhatsApp URL, Mailto URL, and Print PDF action.
     */
    public function test_quotation_details_has_pdf_whatsapp_and_email_channels(): void
    {
        $quotation = FinanceQuotation::create([
            'org_id' => $this->org->id,
            'quotation_number' => 'QT-2026-0001',
            'customer_name' => 'Syarikat Perkasa Sdn Bhd',
            'customer_phone' => '0198887766',
            'customer_email' => 'boss@perkasa.my',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 2000.00,
            'sst_rate' => 8,
            'sst_amount' => 160.00,
            'total_amount' => 2160.00,
        ]);

        FinanceQuotationLine::create([
            'quotation_id' => $quotation->id,
            'description' => 'Konsultasi Digital',
            'quantity' => 1,
            'unit_price' => 2000.00,
            'line_total' => 2000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('finance.quotations.show', $quotation->id));
        $response->assertOk();

        // 1. Check Print PDF capability
        $response->assertSee('window.print()', false);
        $response->assertSee('Cetak PDF');

        // 2. Check WhatsApp URL formatted with country code (60198887766)
        $response->assertSee('https://wa.me/60198887766', false);
        $response->assertSee('Wasap');

        // 3. Check Mailto URL formatted
        $response->assertSee('mailto:boss@perkasa.my', false);
        $response->assertSee('Email');

        // 4. Check Convert button
        $response->assertSee('Tukar Jadi Invois');
    }

    /**
     * Test converting a quotation to an official invoice.
     */
    public function test_can_convert_quotation_to_invoice_seamlessly(): void
    {
        $quotation = FinanceQuotation::create([
            'org_id' => $this->org->id,
            'quotation_number' => 'QT-2026-0002',
            'customer_name' => 'Alpha Global Logistics',
            'customer_phone' => '+601112223334',
            'customer_email' => 'billing@alphaglobal.com',
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 5000.00,
            'sst_rate' => 8,
            'sst_amount' => 400.00,
            'total_amount' => 5400.00,
        ]);

        FinanceQuotationLine::create([
            'quotation_id' => $quotation->id,
            'description' => 'Servis Penghantaran Pantas',
            'quantity' => 5,
            'unit_price' => 1000.00,
            'line_total' => 5000.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('finance.quotations.convert', $quotation->id));

        $quotation->refresh();
        $this->assertEquals('converted', $quotation->status);
        $this->assertNotNull($quotation->converted_invoice_id);

        $invoice = FinanceInvoice::find($quotation->converted_invoice_id);
        $this->assertNotNull($invoice);
        $this->assertEquals($quotation->id, $invoice->quotation_id);
        $this->assertEquals('Alpha Global Logistics', $invoice->customer_name);
        $this->assertEquals('+601112223334', $invoice->customer_phone);
        $this->assertEquals('billing@alphaglobal.com', $invoice->customer_email);
        $this->assertEquals(5000.00, (float) $invoice->subtotal);
        $this->assertEquals(400.00, (float) $invoice->sst_amount);
        $this->assertEquals(5400.00, (float) $invoice->total_amount);
        $this->assertEquals('draft', $invoice->status);

        $this->assertCount(1, $invoice->lines);
        $this->assertEquals('Servis Penghantaran Pantas', $invoice->lines->first()->description);

        $response->assertRedirect(route('finance.invoices.show', $invoice->id));
        $response->assertSessionHas('success');
    }

    /**
     * Test direct invoice creation without quotation, including WhatsApp and Email.
     */
    public function test_can_create_direct_invoice_without_quotation_and_share_via_channels(): void
    {
        $payload = [
            'customer_name' => 'Kedai Runcit Pak Samad',
            'customer_phone' => '017-9998877',
            'customer_email' => 'paksamad@runcit.com',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'sst_rate' => 0,
            'lines' => [
                [
                    'description' => 'Bekalan Beras & Gandum',
                    'quantity' => 10,
                    'unit_price' => 50.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('finance.invoices.store'), $payload);

        $invoice = FinanceInvoice::where('org_id', $this->org->id)->first();
        $this->assertNotNull($invoice);
        $this->assertNull($invoice->quotation_id);
        $this->assertEquals('017-9998877', $invoice->customer_phone);
        $this->assertEquals(500.00, (float) $invoice->total_amount);

        // Check show page for direct invoice has WhatsApp, Email and Print PDF
        $showResponse = $this->actingAs($this->user)->get(route('finance.invoices.show', $invoice->id));
        $showResponse->assertOk();
        $showResponse->assertSee('window.print()', false);
        $showResponse->assertSee('https://wa.me/60179998877', false);
        $showResponse->assertSee('mailto:paksamad@runcit.com', false);
    }

    /**
     * Test quotation status update (e.g. mark as accepted / rejected).
     */
    public function test_can_update_quotation_status(): void
    {
        $quotation = FinanceQuotation::create([
            'org_id' => $this->org->id,
            'quotation_number' => 'QT-2026-0003',
            'customer_name' => 'Klien Test',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'subtotal' => 100.00,
            'sst_rate' => 0,
            'sst_amount' => 0,
            'total_amount' => 100.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('finance.quotations.status', $quotation->id), [
            'status' => 'accepted',
        ]);

        $response->assertRedirect();
        $this->assertEquals('accepted', $quotation->fresh()->status);
    }
}
