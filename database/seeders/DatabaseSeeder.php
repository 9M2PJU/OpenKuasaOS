<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\FinanceAccount;
use App\Models\FinanceExpense;
use App\Models\FinanceInvoice;
use App\Models\FinanceInvoiceLine;
use App\Models\FinanceLedgerAccount;
use App\Models\HireApplication;
use App\Models\HireJob;
use App\Models\HrAttendanceDay;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\Lead;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\Profile;
use App\Models\User;
use App\Services\Finance\BendaharaPostingService;
use App\Services\Finance\SstService;
use App\Services\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Demo Admin User (Ahmad Zulkifli / Demo)
        $user = User::firstOrCreate(
            ['email' => 'admin@openkuasa.my'],
            [
                'name' => 'Ahmad Zulkifli',
                'password' => Hash::make('password'),
                'is_demo' => true,
                'is_superadmin' => false,
                'status' => 'active',
            ]
        );
        $user->update(['is_demo' => true, 'is_superadmin' => false]);

        Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'full_name' => 'Ahmad Zulkifli',
            ]
        );

        // 2. Create Self-Hosted Main Owner & Superadmin (Maui Sabily / Sabily Enterprise)
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@sabily.info'],
            [
                'name' => 'Maui Sabily',
                'password' => Hash::make('password'),
                'is_superadmin' => true,
                'is_demo' => false,
                'status' => 'active',
            ]
        );
        $superadmin->update(['is_superadmin' => true, 'is_demo' => false, 'status' => 'active']);

        Profile::firstOrCreate(
            ['user_id' => $superadmin->id],
            [
                'email' => $superadmin->email,
                'full_name' => 'Maui Sabily',
            ]
        );

        $sabilyOrg = Org::firstOrCreate(
            ['slug' => 'sabily-enterprise'],
            ['name' => 'Sabily Enterprise']
        );

        OrgMember::firstOrCreate(
            ['org_id' => $sabilyOrg->id, 'user_id' => $superadmin->id],
            ['role' => 'owner']
        );

        // 3. Create Default Malaysian SME Organization for Demo User
        $org = Org::firstOrCreate(
            ['slug' => 'rimba-ventures'],
            ['name' => 'Rimba Ventures Sdn Bhd']
        );

        OrgMember::firstOrCreate(
            ['org_id' => $org->id, 'user_id' => $user->id],
            ['role' => 'owner']
        );

        OrganizationContext::set($org);

        // 3. Finance: Bank Accounts
        $maybank = FinanceAccount::firstOrCreate(
            ['org_id' => $org->id, 'name' => 'Maybank Islamic Berhad'],
            [
                'type' => 'bank',
                'account_number' => '5642 1892 0019',
                'currency' => 'MYR',
                'balance' => 84500.00,
            ]
        );

        $cimb = FinanceAccount::firstOrCreate(
            ['org_id' => $org->id, 'name' => 'CIMB Bank Berhad'],
            [
                'type' => 'bank',
                'account_number' => '8009 2314 5590',
                'currency' => 'MYR',
                'balance' => 32400.00,
            ]
        );

        FinanceAccount::firstOrCreate(
            ['org_id' => $org->id, 'name' => 'Petty Cash Pejabat'],
            [
                'type' => 'cash',
                'currency' => 'MYR',
                'balance' => 2500.00,
            ]
        );

        // 4. Finance: Ledger Accounts (Chart of Accounts)
        $coaBank = FinanceLedgerAccount::firstOrCreate(
            ['org_id' => $org->id, 'code' => '1000'],
            ['name' => 'Tunai & Bank', 'type' => 'asset']
        );
        $coaAr = FinanceLedgerAccount::firstOrCreate(
            ['org_id' => $org->id, 'code' => '1100'],
            ['name' => 'Penghutang Perniagaan (Accounts Receivable)', 'type' => 'asset']
        );
        $coaRev = FinanceLedgerAccount::firstOrCreate(
            ['org_id' => $org->id, 'code' => '4000'],
            ['name' => 'Hasil Jualan Perisian', 'type' => 'revenue']
        );
        $coaSst = FinanceLedgerAccount::firstOrCreate(
            ['org_id' => $org->id, 'code' => '2100'],
            ['name' => 'Cukai SST Belum Bayar', 'type' => 'liability']
        );

        // 5. Invoices with SST
        $sstService = new SstService();
        $postingService = new BendaharaPostingService($sstService);

        if (FinanceInvoice::count() === 0) {
            $inv1 = FinanceInvoice::create([
                'org_id' => $org->id,
                'customer_name' => 'Megah Holdings Sdn Bhd',
                'customer_email' => 'finance@megah.my',
                'issue_date' => Carbon::now()->subDays(10)->toDateString(),
                'due_date' => Carbon::now()->addDays(20)->toDateString(),
                'status' => 'draft',
                'subtotal' => 12000.00,
                'sst_rate' => 8.0,
                'sst_amount' => 960.00,
                'total_amount' => 12960.00,
                'e_invoice_status' => 'validated',
                'e_invoice_uuid' => 'MY-INV-2026-9901-8842',
            ]);

            FinanceInvoiceLine::create([
                'invoice_id' => $inv1->id,
                'description' => 'Langganan Tahunan OpenKuasa Enterprise Edition',
                'quantity' => 1,
                'unit_price' => 12000.00,
                'total' => 12000.00,
            ]);

            $postingService->postInvoice($inv1);

            // Record payment for inv1
            $postingService->recordPayment($inv1, $maybank, 12960.00, [
                'payment_date' => Carbon::now()->subDays(2)->toDateString(),
                'reference' => 'MBB-ONLINE-TXN-01',
            ]);

            // Create a posted invoice waiting for payment
            $inv2 = FinanceInvoice::create([
                'org_id' => $org->id,
                'customer_name' => 'Cahaya Logistik Berhad',
                'customer_email' => 'akaun@cahayalogistik.com',
                'issue_date' => Carbon::now()->subDays(5)->toDateString(),
                'due_date' => Carbon::now()->addDays(25)->toDateString(),
                'status' => 'draft',
                'subtotal' => 6500.00,
                'sst_rate' => 6.0,
                'sst_amount' => 390.00,
                'total_amount' => 6890.00,
                'e_invoice_status' => 'pending',
                'e_invoice_uuid' => 'MY-INV-2026-7734-1102',
            ]);

            FinanceInvoiceLine::create([
                'invoice_id' => $inv2->id,
                'description' => 'Servis Integrasi API dan Penyesuaian Modul Lekiu',
                'quantity' => 1,
                'unit_price' => 6500.00,
                'total' => 6500.00,
            ]);

            $postingService->postInvoice($inv2);

            // Seed Standard LHDN Chart of Accounts
            $postingService->seedStandardChartOfAccounts($org->id);

            // Sample Expense 1: Belian Stok & Server (COGS)
            $cogsLedger = FinanceLedgerAccount::where('org_id', $org->id)->where('code', '5000')->first();
            if ($cogsLedger) {
                $exp1 = FinanceExpense::create([
                    'org_id' => $org->id,
                    'account_id' => $maybank->id,
                    'ledger_account_id' => $cogsLedger->id,
                    'voucher_number' => $postingService->generateNextVoucherNumber($org->id),
                    'expense_category' => 'cogs',
                    'tax_classification' => 'cogs',
                    'vendor_name' => 'Cloud Servers Infrastructure Sdn Bhd',
                    'vendor_tin' => 'C1029384910',
                    'vendor_brn' => '202301019922',
                    'payment_date' => Carbon::now()->subDays(7)->toDateString(),
                    'subtotal' => 2500.00,
                    'tax_rate' => 8.0,
                    'tax_amount' => 200.00,
                    'total_amount' => 2700.00,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'INV-CS-8921',
                    'e_invoice_uuid' => 'MY-INV-2026-CLOUD-9901',
                    'e_invoice_status' => 'supplier_validated',
                    'status' => 'draft',
                    'description' => 'Langganan Server Cloud & Jalur Lebar Berkapasiti Tinggi',
                ]);
                $postingService->postExpense($exp1);
            }

            // Sample Expense 2: Sewa Pejabat (OPEX)
            $opexLedger = FinanceLedgerAccount::where('org_id', $org->id)->where('code', '6100')->first();
            if ($opexLedger) {
                $exp2 = FinanceExpense::create([
                    'org_id' => $org->id,
                    'account_id' => $cimb->id,
                    'ledger_account_id' => $opexLedger->id,
                    'voucher_number' => $postingService->generateNextVoucherNumber($org->id),
                    'expense_category' => 'opex',
                    'tax_classification' => 'deductible',
                    'vendor_name' => 'Menara Vista Properties Berhad',
                    'vendor_tin' => 'C8819203911',
                    'vendor_brn' => '201901004455',
                    'payment_date' => Carbon::now()->subDays(3)->toDateString(),
                    'subtotal' => 3200.00,
                    'tax_rate' => 0.0,
                    'tax_amount' => 0.00,
                    'total_amount' => 3200.00,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'RCT-RENT-1026',
                    'e_invoice_uuid' => 'MY-INV-2026-RENT-3312',
                    'e_invoice_status' => 'supplier_validated',
                    'status' => 'draft',
                    'description' => 'Bayaran Sewa Pejabat Tingkat 8 Bulan Semasa',
                ]);
                $postingService->postExpense($exp2);
            }

            // Sample Expense 3: Pembelian Aset Komputer Pembangun (CAPEX)
            $capexLedger = FinanceLedgerAccount::where('org_id', $org->id)->where('code', '1500')->first();
            if ($capexLedger) {
                $exp3 = FinanceExpense::create([
                    'org_id' => $org->id,
                    'account_id' => $maybank->id,
                    'ledger_account_id' => $capexLedger->id,
                    'voucher_number' => $postingService->generateNextVoucherNumber($org->id),
                    'expense_category' => 'capex',
                    'tax_classification' => 'capital_allowance',
                    'vendor_name' => 'Machines Apple Premium Partner',
                    'vendor_tin' => 'C4491028399',
                    'vendor_brn' => '201001008899',
                    'payment_date' => Carbon::now()->subDays(4)->toDateString(),
                    'subtotal' => 8999.00,
                    'tax_rate' => 0.0,
                    'tax_amount' => 0.00,
                    'total_amount' => 8999.00,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'INV-MAC-88192',
                    'e_invoice_uuid' => 'MY-INV-2026-MACHINES-002',
                    'e_invoice_status' => 'supplier_validated',
                    'status' => 'draft',
                    'description' => 'Pembelian MacBook Pro M3 untuk Pembangunan Sistem',
                ]);
                $postingService->postExpense($exp3);
            }
        }

        // 6. People & HR Employees
        $emp1 = HrEmployee::firstOrCreate(
            ['org_id' => $org->id, 'employee_number' => 'EMP-001'],
            [
                'full_name' => 'Irfan Hakim',
                'ic_passport_number' => '940815-14-5511',
                'position' => 'Ketua Pegawai Operasi (COO)',
                'basic_salary' => 6500.00,
                'join_date' => '2024-01-15',
                'status' => 'active',
                'user_id' => $user->id,
            ]
        );

        $emp2 = HrEmployee::firstOrCreate(
            ['org_id' => $org->id, 'employee_number' => 'EMP-002'],
            [
                'full_name' => 'Nurul Aina',
                'ic_passport_number' => '960228-10-6644',
                'position' => 'Eksekutif Kanan Kewangan',
                'basic_salary' => 4500.00,
                'join_date' => '2024-03-01',
                'status' => 'active',
            ]
        );

        $emp3 = HrEmployee::firstOrCreate(
            ['org_id' => $org->id, 'employee_number' => 'EMP-003'],
            [
                'full_name' => 'Daniel Tan',
                'ic_passport_number' => '971104-08-5923',
                'position' => 'Jurutera Perisian Utama',
                'basic_salary' => 5200.00,
                'join_date' => '2024-05-15',
                'status' => 'active',
            ]
        );

        // Attendance records today
        HrAttendanceDay::firstOrCreate(
            ['employee_id' => $emp1->id, 'date' => Carbon::today()->toDateString()],
            [
                'org_id' => $org->id,
                'clock_in' => '08:55:00',
                'status' => 'present',
            ]
        );

        HrAttendanceDay::firstOrCreate(
            ['employee_id' => $emp2->id, 'date' => Carbon::today()->toDateString()],
            [
                'org_id' => $org->id,
                'clock_in' => '09:02:00',
                'status' => 'present',
            ]
        );

        // Leave Requests
        HrLeaveRequest::firstOrCreate(
            ['employee_id' => $emp3->id, 'start_date' => Carbon::tomorrow()->toDateString()],
            [
                'org_id' => $org->id,
                'leave_type' => 'annual',
                'end_date' => Carbon::tomorrow()->toDateString(),
                'days_count' => 1,
                'reason' => 'Urusan peribadi keluarga.',
                'status' => 'pending',
            ]
        );

        // 7. CRM Kasturi Contacts & Deals
        $contact1 = CrmContact::firstOrCreate(
            ['org_id' => $org->id, 'email' => 'faizul@senibinautara.my'],
            [
                'name' => 'Ir. Faizul Hadi',
                'company' => 'Kumpulan Senibina Utara Sdn Bhd',
                'phone' => '+6012-9988771',
            ]
        );

        $contact2 = CrmContact::firstOrCreate(
            ['org_id' => $org->id, 'email' => 'suhaila@selangortek.com'],
            [
                'name' => 'Puan Suhaila Mansor',
                'company' => 'Selangor Tech Solutions',
                'phone' => '+6019-3322110',
            ]
        );

        CrmDeal::firstOrCreate(
            ['org_id' => $org->id, 'title' => 'Pakej Pengautomatan Operasi HR & Payroll'],
            [
                'contact_id' => $contact1->id,
                'amount' => 38000.00,
                'stage_id' => 'proposal',
                'probability' => 70,
                'expected_close_date' => Carbon::now()->addDays(21)->toDateString(),
            ]
        );

        CrmDeal::firstOrCreate(
            ['org_id' => $org->id, 'title' => 'Langganan CRM & Papan Iklan Kasturi'],
            [
                'contact_id' => $contact2->id,
                'amount' => 19500.00,
                'stage_id' => 'negotiation',
                'probability' => 85,
                'expected_close_date' => Carbon::now()->addDays(14)->toDateString(),
            ]
        );

        // 8. Reach Jebat Campaigns & Leads
        $this->call(JebatDemoSeeder::class);

        // 8b. Kasturi CRM AI Agents, Runs & Deals
        $this->call(KasturiDemoSeeder::class);

        // 8c. Lekiu HR Employees, Attendance & Leave
        $this->call(LekiuDemoSeeder::class);

        // 9. Hire Lekir Jobs & Applications
        $job1 = HireJob::firstOrCreate(
            ['org_id' => $org->id, 'title' => 'Jurutera Backend Kanan (PHP/Laravel & PostgreSQL)'],
            [
                'department' => 'Kejuruteraan Perisian',
                'employment_type' => 'full_time',
                'description' => 'Membangunkan sistem enterprise multi-tenant dengan kestabilan lejar am dan API berprestasi tinggi.',
                'status' => 'published',
            ]
        );

        HireApplication::firstOrCreate(
            ['job_id' => $job1->id, 'email' => 'amirul.dev@gmail.com'],
            [
                'candidate_name' => 'Amirul Asyraf',
                'phone' => '+6013-4455667',
                'stage' => 'screening',
            ]
        );
    }
}
