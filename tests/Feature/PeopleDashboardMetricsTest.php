<?php

namespace Tests\Feature;

use App\Models\HrAttendanceDay;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Database\Seeders\LekiuDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleDashboardMetricsTest extends TestCase
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

    public function test_people_dashboard_renders_metrics_and_charts_from_database_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        // Seed Lekiu demo dataset
        $this->seed(LekiuDemoSeeder::class);

        $response = $this->get('/people');
        $response->assertOk();

        // 1. Top 4 KPI Cards
        $response->assertSee('Headcount');
        $response->assertSee('20');
        $response->assertSee('Clocked in so far today');
        $response->assertSee('16');
        $response->assertSee('On leave today');
        $response->assertSee('3');
        $response->assertSee('Pending approvals');
        $response->assertSee('6');

        // 2. Charts
        $response->assertSee('Headcount over time');
        $response->assertSee('Last 8 months, by join date');
        $response->assertSee('headcountOverTimeChart');
        $response->assertSee('By department');
        $response->assertSee('byDepartmentChart');
        $response->assertSee('Sales');
        $response->assertSee('Operations');
        $response->assertSee('Finance');
        $response->assertSee('Management');
        $response->assertSee('Marketing');

        // 3. Middle Cards
        $response->assertSee('Leave by type');
        $response->assertSee('Approved days this month');
        $response->assertSee('Annual leave');
        $response->assertSee('Emergency leave');
        $response->assertSee('Medical leave');
        $response->assertSee('Clocked in so far');
        $response->assertSee('Present or late today, not yet a rate');
        $response->assertSee('HR AI agents');
        $response->assertSee('Your always-on crew');
        $response->assertSee('Leave Approver');
        $response->assertSee('Payroll Assistant');
        $response->assertSee('Onboarding Guide');
        $response->assertSee('Attendance Monitor');

        // 4. Bottom Activity Cards
        $response->assertSee('On leave today');
        $response->assertSee('Lim Wei Jie');
        $response->assertSee('Medical leave');
        $response->assertSee('Pending approvals');
        $response->assertSee('Tan Mei Ling');
        $response->assertSee('Birthdays & anniversaries', false);
        $response->assertSee('Nurul Huda');
        $response->assertSee('Birthday');
        $response->assertSee('23 Oct');
    }

    public function test_people_dashboard_dynamically_calculates_from_database(): void
    {
        $this->actingAs($this->user);

        $this->seed(LekiuDemoSeeder::class);

        // Add 1 extra employee to DB
        HrEmployee::create([
            'org_id' => $this->org->id,
            'employee_number' => 'EMP-021',
            'full_name' => 'Siti Nurhaliza',
            'ic_passport_number' => '850111-10-1234',
            'position' => 'Senior Sales Executive',
            'join_date' => Carbon::today()->toDateString(),
            'basic_salary' => 5500.00,
            'epf_rate' => 11.00,
            'status' => 'active',
        ]);

        $response = $this->get('/people');
        $response->assertOk();
        // Headcount should now dynamically reflect 21
        $response->assertSee('21');
    }
}
