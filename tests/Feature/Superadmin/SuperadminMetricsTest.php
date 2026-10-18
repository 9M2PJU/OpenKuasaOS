<?php

namespace Tests\Feature\Superadmin;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Superadmin\SuperadminMetricsService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected SuperadminMetricsService $metricsService;
    protected User $superadmin;
    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metricsService = app(SuperadminMetricsService::class);

        $this->org = Org::create([
            'name' => 'Sabily Enterprise',
            'slug' => 'sabily-enterprise',
        ]);

        $this->superadmin = User::create([
            'name' => 'Maui Sabily',
            'email' => 'admin@sabily.info',
            'password' => bcrypt('password123'),
            'is_superadmin' => true,
            'is_demo' => false,
            'status' => 'active',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->superadmin->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);
    }

    /**
     * Metrik mengira jumlah sebenar pendaftaran pengguna dan organisasi dengan tepat.
     */
    public function test_metrics_service_accurately_counts_users_and_organizations(): void
    {
        // Initially 1 user, 1 org
        $this->assertEquals(1, $this->metricsService->getTotalUsersCount());
        $this->assertEquals(1, $this->metricsService->getTotalOrganizationsCount());

        $breakdown = $this->metricsService->getUsersBreakdown();
        $this->assertEquals(1, $breakdown['total']);
        $this->assertEquals(1, $breakdown['real']);
        $this->assertEquals(0, $breakdown['demo']);

        // Add 1 demo user
        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password123'),
            'is_demo' => true,
            'is_superadmin' => false,
            'status' => 'active',
        ]);

        // Add 1 more org
        $secondOrg = Org::create([
            'name' => 'Kedua Sdn Bhd',
            'slug' => 'kedua-sdn-bhd',
        ]);

        $this->assertEquals(2, $this->metricsService->getTotalUsersCount());
        $this->assertEquals(2, $this->metricsService->getTotalOrganizationsCount());

        $updatedBreakdown = $this->metricsService->getUsersBreakdown();
        $this->assertEquals(2, $updatedBreakdown['total']);
        $this->assertEquals(1, $updatedBreakdown['real']);
        $this->assertEquals(1, $updatedBreakdown['demo']);
    }

    /**
     * Pendaftaran akaun baharu melalui /register menambah kaunter pengguna dan organisasi +1 secara langsung.
     */
    public function test_new_user_registration_increments_dashboard_metrics_counter(): void
    {
        $initialUsers = $this->metricsService->getTotalUsersCount();
        $initialOrgs = $this->metricsService->getTotalOrganizationsCount();

        // Register new account through public registration endpoint
        $response = $this->post('/register', [
            'name' => 'Ahmad Razak',
            'company_name' => 'Razak Solution Sdn Bhd',
            'email' => 'razak@solution.my',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/dashboard');

        // Check metrics counter increased by exactly 1
        $this->assertEquals($initialUsers + 1, $this->metricsService->getTotalUsersCount());
        $this->assertEquals($initialOrgs + 1, $this->metricsService->getTotalOrganizationsCount());

        // Check superadmin dashboard displays new counts
        $dashboardResponse = $this->actingAs($this->superadmin)->get('/superadmin');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee((string) ($initialUsers + 1));
        $dashboardResponse->assertSee((string) ($initialOrgs + 1));
    }

    /**
     * getSystemHealth returns essential infrastructure data.
     */
    public function test_system_health_returns_database_and_runtime_info(): void
    {
        $health = $this->metricsService->getSystemHealth();

        $this->assertArrayHasKey('php_version', $health);
        $this->assertArrayHasKey('laravel_version', $health);
        $this->assertArrayHasKey('database', $health);
        $this->assertTrue($health['database']['connected']);
        $this->assertArrayHasKey('storage', $health);
        $this->assertArrayHasKey('ai', $health);
    }

    /**
     * API Telemetry metrics endpoint returns accurate JSON data.
     */
    public function test_api_telemetry_metrics_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->superadmin, 'sanctum')->getJson('/api/v1/superadmin/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_users',
                'total_orgs',
                'active_users_24h',
                'storage_mb',
            ],
        ]);
    }
}
