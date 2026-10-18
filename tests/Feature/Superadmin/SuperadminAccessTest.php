<?php

namespace Tests\Feature\Superadmin;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $demoUser;
    protected User $superadminUser;
    protected User $regularUser;
    protected Org $demoOrg;
    protected Org $superadminOrg;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Demo User (First account: Ahmad Zulkifli / Rimba Ventures)
        $this->demoUser = User::create([
            'name' => 'Ahmad Zulkifli',
            'email' => 'admin@openkuasa.my',
            'password' => bcrypt('password123'),
            'is_demo' => true,
            'is_superadmin' => false,
            'status' => 'active',
        ]);

        $this->demoOrg = Org::create([
            'name' => 'Rimba Ventures Sdn Bhd',
            'slug' => 'rimba-ventures',
        ]);

        OrgMember::create([
            'org_id' => $this->demoOrg->id,
            'user_id' => $this->demoUser->id,
            'role' => 'owner',
        ]);

        // 2. Superadmin User (Second account: Maui Sabily / Sabily Enterprise)
        $this->superadminUser = User::create([
            'name' => 'Maui Sabily',
            'email' => 'admin@sabily.info',
            'password' => bcrypt('password123'),
            'is_demo' => false,
            'is_superadmin' => true,
            'status' => 'active',
        ]);

        $this->superadminOrg = Org::create([
            'name' => 'Sabily Enterprise',
            'slug' => 'sabily-enterprise',
        ]);

        OrgMember::create([
            'org_id' => $this->superadminOrg->id,
            'user_id' => $this->superadminUser->id,
            'role' => 'owner',
        ]);

        // 3. Regular non-superadmin user
        $this->regularUser = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@ctnurhaliza.my',
            'password' => bcrypt('password123'),
            'is_demo' => false,
            'is_superadmin' => false,
            'status' => 'active',
        ]);
    }

    /**
     * Unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/superadmin');
        $response->assertRedirect('/login');
    }

    /**
     * QA Test 1.1: admin@openkuasa.my (demo) -> 403 Forbidden
     */
    public function test_demo_user_is_forbidden_from_superadmin(): void
    {
        $response = $this->actingAs($this->demoUser)
            ->withSession(['current_org_id' => $this->demoOrg->id])
            ->get('/superadmin');

        $response->assertStatus(403);
    }

    /**
     * Regular user -> 403 Forbidden
     */
    public function test_regular_user_is_forbidden_from_superadmin(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/superadmin');

        $response->assertStatus(403);
    }

    /**
     * QA Test 1.2: admin@sabily.info (superadmin) -> 200 OK
     */
    public function test_superadmin_has_full_access_to_dashboard(): void
    {
        $response = $this->actingAs($this->superadminUser)
            ->withSession(['current_org_id' => $this->superadminOrg->id])
            ->get('/superadmin');

        $response->assertStatus(200);
        $response->assertSee('Pusat Kawalan Superadmin');
        $response->assertSee('Pentadbiran Utama');
        $response->assertSee('[SUPERADMIN]');
    }

    /**
     * Demo user sees the demo banner and demo badge in app layout.
     */
    public function test_demo_user_sees_demo_banner_and_badge(): void
    {
        OrganizationContext::set($this->demoOrg);

        $response = $this->actingAs($this->demoUser)
            ->withSession(['current_org_id' => $this->demoOrg->id])
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Anda sedang menggunakan Akaun Demo (Ahmad Zulkifli / Rimba Ventures)');
        $response->assertSee('[DEMO MODE]');
        $response->assertDontSee('Pentadbiran Utama');
    }
}
