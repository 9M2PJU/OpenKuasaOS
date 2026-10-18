<?php

namespace Tests\Feature\Superadmin;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Superadmin\ImpersonationService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected Org $superadminOrg;
    protected Org $clientOrg;
    protected User $superadmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Superadmin's original organization
        $this->superadminOrg = Org::create([
            'name' => 'Sabily Enterprise (Host)',
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
            'org_id' => $this->superadminOrg->id,
            'user_id' => $this->superadmin->id,
            'role' => 'owner',
        ]);

        // 2. Client tenant organization (Superadmin is NOT a member here)
        $this->clientOrg = Org::create([
            'name' => 'Kembara Logistics Sdn Bhd',
            'slug' => 'kembara-logistics',
        ]);

        $clientOwner = User::create([
            'name' => 'Tuan Kembara',
            'email' => 'kembara@logistics.my',
            'password' => bcrypt('password123'),
            'is_superadmin' => false,
            'is_demo' => false,
            'status' => 'active',
        ]);

        OrgMember::create([
            'org_id' => $this->clientOrg->id,
            'user_id' => $clientOwner->id,
            'role' => 'owner',
        ]);

        // 3. Regular non-superadmin user
        $this->regularUser = User::create([
            'name' => 'Pekerja Biasa',
            'email' => 'pekerja@example.com',
            'password' => bcrypt('password123'),
            'is_superadmin' => false,
            'is_demo' => false,
            'status' => 'active',
        ]);

        OrganizationContext::set($this->superadminOrg);
    }

    /**
     * Superadmin boleh bertukar konteks organisasi untuk bantuan teknikal.
     */
    public function test_superadmin_can_impersonate_client_organization(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->withSession(['current_org_id' => $this->superadminOrg->id])
            ->post(route('superadmin.organizations.impersonate', $this->clientOrg->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('info');
        $response->assertSessionHas('impersonated_org_id', $this->clientOrg->id);
        $response->assertSessionHas('original_org_id', $this->superadminOrg->id);
        $response->assertSessionHas('current_org_id', $this->clientOrg->id);

        $this->assertEquals($this->clientOrg->id, OrganizationContext::getId());
    }

    /**
     * Ketika dalam mod impersonasi, Superadmin boleh melihat papan pemuka organisasi sasaran dan melihat banner bantuan.
     */
    public function test_impersonating_superadmin_sees_target_organization_dashboard_and_banner(): void
    {
        $dashboardResponse = $this->actingAs($this->superadmin)
            ->withSession([
                'current_org_id' => $this->clientOrg->id,
                'impersonated_org_id' => $this->clientOrg->id,
                'impersonated_org_name' => $this->clientOrg->name,
                'original_org_id' => $this->superadminOrg->id,
            ])
            ->get(route('dashboard'));

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($this->clientOrg->name);
        $dashboardResponse->assertSee('Keluar Dari Mod Bantuan Organisasi');
    }

    /**
     * Superadmin boleh kembali ke sesi organisasi asal dengan selamat.
     */
    public function test_superadmin_can_leave_impersonation_mode(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->withSession([
                'current_org_id' => $this->clientOrg->id,
                'impersonated_org_id' => $this->clientOrg->id,
                'impersonated_org_name' => $this->clientOrg->name,
                'original_org_id' => $this->superadminOrg->id,
            ])
            ->post(route('superadmin.impersonate.leave'));

        $response->assertRedirect(route('superadmin.organizations.index'));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('impersonated_org_id');
        $response->assertSessionMissing('original_org_id');
        $response->assertSessionHas('current_org_id', $this->superadminOrg->id);

        $this->assertEquals($this->superadminOrg->id, OrganizationContext::getId());
    }

    /**
     * Pengguna bukan superadmin disekat daripada memulakan impersonasi.
     */
    public function test_non_superadmin_is_forbidden_from_impersonating(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('superadmin.organizations.impersonate', $this->clientOrg->id));

        $response->assertStatus(403);
    }
}
