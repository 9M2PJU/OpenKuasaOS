<?php

namespace Tests\Feature;

use App\Models\FinanceAccount;
use App\Models\FinanceInvoice;
use App\Models\HrEmployee;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebModuleViewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;
    protected HrEmployee $employee;
    protected FinanceAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Ahmad Pengurus',
            'email' => 'ahmad@syarikat.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Syarikat Maju Jaya Sdn Bhd',
            'slug' => 'syarikat-maju-jaya',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);

        $this->employee = HrEmployee::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'employee_number' => 'EMP-001',
            'full_name' => 'Ahmad Pengurus',
            'ic_passport_number' => '900101-14-1234',
            'position' => 'Pengurus Operasi',
            'basic_salary' => 5000.00,
            'join_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $this->bankAccount = FinanceAccount::create([
            'org_id' => $this->org->id,
            'name' => 'Maybank Syarikat',
            'type' => 'bank',
            'currency' => 'MYR',
            'balance' => 20000.00,
        ]);
    }

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('Log Masuk');
        $this->get('/register')->assertStatus(200)->assertSee('Daftar Akaun Syarikat');
    }

    public function test_user_can_register_new_company_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siti Khadijah',
            'company_name' => 'Khadijah Trading Sdn Bhd',
            'email' => 'khadijah@trading.my',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'khadijah@trading.my']);
        $this->assertDatabaseHas('orgs', ['name' => 'Khadijah Trading Sdn Bhd']);
    }

    public function test_authenticated_user_can_access_all_module_screens(): void
    {
        $this->actingAs($this->user);

        // Core Dashboard & Tuah
        $this->get('/dashboard')->assertStatus(200)->assertSee('Pusat Kawalan Perniagaan');
        $this->get('/command')->assertStatus(200)->assertSee('Pusat Arahan Pintar Merentas Modul');

        // Bendahara (Finance)
        $this->get('/finance')->assertStatus(200)
            ->assertSee('Bendahara')
            ->assertSee('Cash balance')
            ->assertSee('Revenue · MTD')
            ->assertSee('Outstanding · AR')
            ->assertSee('Payable · AP')
            ->assertSee('Cash in vs out')
            ->assertSee('Revenue by category')
            ->assertSee('Collections')
            ->assertSee('Outstanding by customer')
            ->assertSee('Collection rate')
            ->assertSee('Overdue invoices')
            ->assertSee('e-Invois · LHDN MyInvois');
        $this->get('/finance/invoices')->assertStatus(200)->assertSee('Invois Jualan');
        $this->get('/finance/invoices/create')->assertStatus(200)->assertSee('Cipta Draf Invois Baru');
        $this->get('/finance/accounts')->assertStatus(200)->assertSee('Akaun Bank');
        $this->get('/finance/journals')->assertStatus(200)->assertSee('Audit Jurnal');

        // Lekiu (People & HR)
        $this->get('/people')->assertStatus(200)->assertSee('Lekiu');
        $this->get('/people/employees')->assertStatus(200)->assertSee('Direktori Tenaga Kerja');
        $this->get('/people/attendance')->assertStatus(200)->assertSee('Sistem Punch Card Kehadiran');
        $this->get('/people/leave')->assertStatus(200)->assertSee('Pengurusan Cuti');
        $this->get('/people/payroll')->assertStatus(200)->assertSee('Enjin Penggajian Berkanun Malaysia');

        // Kasturi (CRM)
        $this->get('/crm')->assertStatus(200)->assertSee('Kasturi');
        $this->get('/crm/deals')->assertStatus(200)->assertSee('Papan Saluran Urus Niaga');
        $this->get('/crm/contacts')->assertStatus(200)->assertSee('Buku Kontak Pelanggan');

        // Jebat (Reach)
        $this->get('/reach')->assertStatus(200)->assertSee('Jebat');
        $this->get('/reach/campaigns')->assertStatus(200)->assertSee('Kempen Pengiklanan Digital');
        $this->get('/reach/leads')->assertStatus(200)->assertSee('Pangkalan Data Prospek');

        // Lekir (Hire)
        $this->get('/hire')->assertStatus(200)->assertSee('Lekir');
        $this->get('/hire/jobs')->assertStatus(200)->assertSee('Iklan Jawatan Kosong');
        $this->get('/hire/applications')->assertStatus(200)->assertSee('Calon Pemohon Pekerjaan');
        $this->get('/hire/interviews')->assertStatus(200)->assertSee('Jadual Sesi Temu Duga');

        // Tetapan (Settings & Language)
        $this->get('/settings')->assertStatus(200)->assertSee('Tetapan Sistem')->assertSee('Bahasa Melayu');
    }

    public function test_attendance_clock_in_and_clock_out(): void
    {
        $this->actingAs($this->user);

        // Clock In
        $this->post('/people/attendance/clock-in')
            ->assertRedirect();

        $this->assertDatabaseHas('hr_attendance_days', [
            'employee_id' => $this->employee->id,
            'status' => 'present',
        ]);

        // Clock Out
        $this->post('/people/attendance/clock-out')
            ->assertRedirect();

        $attendance = \App\Models\HrAttendanceDay::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_web_invoice_creation_post_and_payment(): void
    {
        $this->actingAs($this->user);

        // 1. Create Invoice Draft via web form
        $response = $this->post('/finance/invoices', [
            'customer_name' => 'Pelanggan Ujian Sdn Bhd',
            'customer_email' => 'akaun@pelanggan.my',
            'issue_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
            'sst_rate' => 8,
            'lines' => [
                [
                    'description' => 'Servis Pembangunan Sistem',
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                ],
            ],
        ]);

        $response->assertRedirect('/finance/invoices');
        $invoice = FinanceInvoice::where('customer_name', 'Pelanggan Ujian Sdn Bhd')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(1080.00, $invoice->total_amount);

        // 2. Post the invoice
        $this->post("/finance/invoices/{$invoice->id}/post")
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('posted', $invoice->status);
        $this->assertNotNull($invoice->invoice_number);

        // 3. Record payment
        $this->post("/finance/invoices/{$invoice->id}/payments", [
            'account_id' => $this->bankAccount->id,
            'amount' => 1080.00,
            'payment_date' => Carbon::now()->toDateString(),
            'reference' => 'MBB-REF-1234',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_crm_deal_creation_and_stage_update(): void
    {
        $this->actingAs($this->user);

        // Create deal
        $this->post('/crm/deals', [
            'title' => 'Tawaran Projek Kerajaan',
            'amount' => 50000.00,
            'stage_id' => 'lead',
        ])->assertRedirect();

        $deal = \App\Models\CrmDeal::where('title', 'Tawaran Projek Kerajaan')->first();
        $this->assertNotNull($deal);

        // Update stage to proposal
        $this->post("/crm/deals/{$deal->id}/stage", [
            'stage_id' => 'proposal',
        ])->assertRedirect();

        $deal->refresh();
        $this->assertEquals('proposal', $deal->stage_id);
    }

    public function test_command_chat_interaction(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/command/chat', [
            'prompt' => 'Berapakah baki tunai syarikat semasa?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'role' => 'user',
            'content' => 'Berapakah baki tunai syarikat semasa?',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'role' => 'assistant',
        ]);
    }

    public function test_sidebar_has_collapsible_toggle_and_ordered_modules(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');
        $response->assertOk();

        // Check collapsible toggle attributes exist
        $response->assertSee('toggleSidebar()', false);
        $response->assertSee('openkuasa_sidebar_collapsed', false);

        // Check modules order: Jebat -> Kasturi -> Lekiu -> Lekir -> Bendahara
        $content = $response->getContent();
        $posJebat = strpos($content, 'Jebat (Iklan');
        $posKasturi = strpos($content, 'Kasturi (CRM');
        $posLekiu = strpos($content, 'Lekiu (HR');
        $posLekir = strpos($content, 'Lekir (Pengambilan');
        $posBendahara = strpos($content, 'Bendahara (Kewangan)');

        $this->assertNotFalse($posJebat, 'Jebat not found in sidebar');
        $this->assertNotFalse($posKasturi, 'Kasturi not found in sidebar');
        $this->assertNotFalse($posLekiu, 'Lekiu not found in sidebar');
        $this->assertNotFalse($posLekir, 'Lekir not found in sidebar');
        $this->assertNotFalse($posBendahara, 'Bendahara not found in sidebar');

        $this->assertTrue($posJebat < $posKasturi, 'Jebat must appear before Kasturi');
        $this->assertTrue($posKasturi < $posLekiu, 'Kasturi must appear before Lekiu');
        $this->assertTrue($posLekiu < $posLekir, 'Lekiu must appear before Lekir');
        $this->assertTrue($posLekir < $posBendahara, 'Lekir must appear before Bendahara');
    }

    public function test_hire_lekir_dashboard_renders_metrics_and_charts_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/hire');
        $response->assertOk();

        // 1. Top KPI metrics
        $response->assertSee('Open roles');
        $response->assertSee('Applications');
        $response->assertSee('Interviews');
        $response->assertSee('Offers out');

        // 2. Analytics charts
        $response->assertSee('Applications over time');
        $response->assertSee('Last 8 weeks');
        $response->assertSee('applicationsOverTimeChart');
        $response->assertSee('Applications by source');
        $response->assertSee('applicationsBySourceChart');

        // 3. Middle Pipeline & AI Crew
        $response->assertSee('Hiring pipeline');
        $response->assertSee('AI recruiters');
        $response->assertSee('Resume Screener');
        $response->assertSee('Interview Scheduler');
        $response->assertSee('JD Writer');
        $response->assertSee('Sourcing Bot');
        $response->assertSee('Offer-accept rate');
        $response->assertSee('Not available yet');

        // 4. Upcoming interviews & Top candidates
        $response->assertSee('Upcoming interviews');
        $response->assertSee('Azlan Wong');
        $response->assertSee('Nabila Idris');
        $response->assertSee('Top candidates');
        $response->assertSee('Azlan Omar');
        $response->assertSee('Nabila Tan');
    }

    public function test_hire_applications_renders_metrics_charts_and_recent_candidates_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/hire/applications');
        $response->assertOk();

        // 1. Top KPI metrics
        $response->assertSee('Total applications');
        $response->assertSee('248');
        $response->assertSee('New');
        $response->assertSee('87');
        $response->assertSee('In review');
        $response->assertSee('44');
        $response->assertSee('Rejected');
        $response->assertSee('82');

        // 2. Charts
        $response->assertSee('By status');
        $response->assertSee('Current application mix');
        $response->assertSee('applicationsByStatusChart');
        $response->assertSee('Applications by job');
        $response->assertSee('Top roles');
        $response->assertSee('applicationsByJobChart');

        // 3. Table Candidates matching screenshot
        $response->assertSee('Recent applications');
        $response->assertSee('Most recent first');
        $response->assertSee('Li Fen Idris');
        $response->assertSee('Customer Support');
        $response->assertSee('Faiz Idris');
        $response->assertSee('Graphic Designer');
        $response->assertSee('Siti Lim');
        $response->assertSee('Daniel Aminah');
        $response->assertSee('Account Manager');
        $response->assertSee('Imran Omar');
        $response->assertSee('Mei Ling Omar');
        $response->assertSee('Sales Executive');
        $response->assertSee('Wei Jie Huda');
        $response->assertSee('Arjun Yusof');
        $response->assertSee('Accountant');
        $response->assertSee('Amira Kumar');

        // 4. Sources & Statuses
        $response->assertSee('LinkedIn');
        $response->assertSee('JobStreet');
        $response->assertSee('Shortlisted');
    }
}
