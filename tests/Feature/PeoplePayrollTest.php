<?php

namespace Tests\Feature;

use App\Models\HrEmployee;
use App\Models\HrPayrollRun;
use App\Models\HrPayslip;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Database\Seeders\LekiuDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeoplePayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Ahmad Zulkifli',
            'email' => 'admin@openkuasa.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Rimba Ventures Sdn Bhd',
            'slug' => 'rimba-ventures',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);
    }

    public function test_user_can_view_payroll_page(): void
    {
        $this->actingAs($this->user);
        $this->seed(LekiuDemoSeeder::class);

        $response = $this->get('/people/payroll');
        $response->assertOk();
        $response->assertSee('Payroll');
    }

    public function test_user_can_generate_payroll_without_error(): void
    {
        $this->actingAs($this->user);
        $this->seed(LekiuDemoSeeder::class);

        $payload = [
            'period_month' => 10,
            'period_year' => 2026,
        ];

        $response = $this->post('/people/payroll/generate', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check HrPayrollRun created
        $run = HrPayrollRun::where('org_id', $this->org->id)
            ->where('period_month', 10)
            ->where('period_year', 2026)
            ->first();

        $this->assertNotNull($run);
        $this->assertGreaterThan(0, $run->total_gross);
        $this->assertGreaterThan(0, $run->total_statutory);
        $this->assertGreaterThan(0, $run->total_net);

        // Check HrPayslip created for employees
        $payslipsCount = HrPayslip::where('payroll_run_id', $run->id)->count();
        $this->assertEquals(20, $payslipsCount);

        // Test idempotency: re-running does not crash or duplicate
        $response2 = $this->post('/people/payroll/generate', $payload);
        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        $runsCount = HrPayrollRun::where('org_id', $this->org->id)
            ->where('period_month', 10)
            ->where('period_year', 2026)
            ->count();
        $this->assertEquals(1, $runsCount);

        // Check eye icon exists on payroll view
        $responseView = $this->get('/people/payroll');
        $responseView->assertOk();
        $firstPayslip = HrPayslip::where('payroll_run_id', $run->id)->first();
        $responseView->assertSee(route('people.payroll.payslip', $firstPayslip->id));
        $responseView->assertSee('Slip Gaji');
    }

    public function test_user_can_view_malaysian_standard_payslip_and_share_whatsapp(): void
    {
        $this->actingAs($this->user);
        $this->seed(LekiuDemoSeeder::class);

        // Generate payroll
        $this->post('/people/payroll/generate', [
            'period_month' => 10,
            'period_year' => 2026,
        ]);

        $run = HrPayrollRun::where('org_id', $this->org->id)->first();
        $payslip = HrPayslip::where('payroll_run_id', $run->id)->first();

        // View the payslip
        $response = $this->get("/people/payroll/payslips/{$payslip->id}");
        $response->assertOk();

        // 1. Check Malaysian standard statutory elements
        $response->assertSee('Penyata Gaji / Payslip');
        $response->assertSee('Maklumat Pekerja');
        $response->assertSee('Pendapatan (Earnings)');
        $response->assertSee('Gaji Asas / Pokok (Basic Salary)');
        $response->assertSee('Potongan Pekerja (Deductions)');
        $response->assertSee('KWSP / EPF Pekerja');
        $response->assertSee('PERKESO / SOCSO Pekerja');
        $response->assertSee('Sistem Insurans Pekerjaan (SIP/EIS)');
        $response->assertSee('PCB LHDN');
        $response->assertSee('JUMLAH GAJI BERSIH DITERIMA (NET PAY)');

        // 2. Check Action Buttons (Print/PDF & WhatsApp Share)
        $response->assertSee('Cetak / Muat Turun PDF');
        $response->assertSee('Kongsi ke WhatsApp');
        $response->assertSee('https://wa.me/?text=');
    }
}
