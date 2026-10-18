<?php

namespace Tests\Feature\Superadmin;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $superadmin;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->targetUser = User::create([
            'name' => 'Kamarul Ariffin',
            'email' => 'kamarul@example.com',
            'password' => bcrypt('targetpassword'),
            'is_superadmin' => false,
            'is_demo' => false,
            'status' => 'active',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->targetUser->id,
            'role' => 'member',
        ]);

        OrganizationContext::set($this->org);
    }

    /**
     * Superadmin boleh menukar status pengguna (Active -> Suspended).
     */
    public function test_superadmin_can_suspend_user_account(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.status', $this->targetUser->id), [
                'status' => 'suspended',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->targetUser->refresh();
        $this->assertEquals('suspended', $this->targetUser->status);
        $this->assertFalse($this->targetUser->isActive());
    }

    /**
     * Superadmin boleh mengaktifkan semula status pengguna yang digantung.
     */
    public function test_superadmin_can_activate_suspended_user(): void
    {
        $this->targetUser->update(['status' => 'suspended']);

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.status', $this->targetUser->id), [
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->targetUser->refresh();
        $this->assertEquals('active', $this->targetUser->status);
        $this->assertTrue($this->targetUser->isActive());
    }

    /**
     * Pengguna dengan status 'suspended' gagal log masuk.
     */
    public function test_suspended_user_fails_to_login_with_specific_error(): void
    {
        $this->targetUser->update(['status' => 'suspended']);

        $response = $this->post('/login', [
            'email' => 'kamarul@example.com',
            'password' => 'targetpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Check error message matches specification
        $errors = session('errors')->get('email');
        $this->assertContains('Akaun anda telah digantung oleh pentadbir sistem.', $errors);
    }

    /**
     * Superadmin boleh melantik pengguna lain sebagai Superadmin.
     */
    public function test_superadmin_can_promote_user_to_superadmin(): void
    {
        $this->assertFalse($this->targetUser->isSuperadmin());

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.toggle-superadmin', $this->targetUser->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->targetUser->refresh();
        $this->assertTrue($this->targetUser->isSuperadmin());
    }

    /**
     * Superadmin boleh melucutkan hak Superadmin pengguna lain.
     */
    public function test_superadmin_can_demote_superadmin(): void
    {
        $this->targetUser->update(['is_superadmin' => true]);

        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.toggle-superadmin', $this->targetUser->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->targetUser->refresh();
        $this->assertFalse($this->targetUser->isSuperadmin());
    }

    /**
     * Superadmin tidak boleh melucutkan hak Superadmin sendiri atau menggantung diri sendiri.
     */
    public function test_superadmin_cannot_self_demote_or_self_suspend(): void
    {
        $demoteResponse = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.toggle-superadmin', $this->superadmin->id));

        $demoteResponse->assertSessionHas('error');
        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->isSuperadmin());

        $suspendResponse = $this->actingAs($this->superadmin)
            ->post(route('superadmin.users.status', $this->superadmin->id), [
                'status' => 'suspended',
            ]);

        $suspendResponse->assertSessionHas('error');
        $this->superadmin->refresh();
        $this->assertEquals('active', $this->superadmin->status);
    }

    /**
     * Superadmin boleh melihat senarai pengguna berserta paparan tab status dan counts.
     */
    public function test_superadmin_can_view_users_index_page_with_counts(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('superadmin.users.index'));

        $response->assertOk();
        $response->assertViewIs('superadmin.users.index');
        $response->assertViewHas('counts');
        $response->assertViewHas('users');

        $counts = $response->viewData('counts');
        $this->assertIsArray($counts);
        $this->assertArrayHasKey('all', $counts);
        $this->assertArrayHasKey('active', $counts);
        $this->assertArrayHasKey('suspended', $counts);
        $this->assertArrayHasKey('demo', $counts);
        $this->assertArrayHasKey('superadmin', $counts);

        $this->assertEquals(2, $counts['all']);
        $this->assertEquals(2, $counts['active']);
        $this->assertEquals(0, $counts['suspended']);
        $this->assertEquals(0, $counts['demo']);
        $this->assertEquals(1, $counts['superadmin']);

        $response->assertSee('Semua (2)');
        $response->assertSee('Aktif (2)');
        $response->assertSee('Digantung (0)');
    }

    /**
     * Superadmin boleh menapis pengguna mengikut status dan kategori.
     */
    public function test_superadmin_can_filter_users_by_status(): void
    {
        // Gantung target user
        $this->targetUser->update(['status' => 'suspended']);

        // Tapis aktif
        $activeResponse = $this->actingAs($this->superadmin)
            ->get(route('superadmin.users.index', ['status' => 'active']));
        $activeResponse->assertOk();
        $this->assertTrue($activeResponse->viewData('users')->contains('id', $this->superadmin->id));
        $this->assertFalse($activeResponse->viewData('users')->contains('id', $this->targetUser->id));

        // Tapis digantung
        $suspendedResponse = $this->actingAs($this->superadmin)
            ->get(route('superadmin.users.index', ['status' => 'suspended']));
        $suspendedResponse->assertOk();
        $this->assertTrue($suspendedResponse->viewData('users')->contains('id', $this->targetUser->id));
        $this->assertFalse($suspendedResponse->viewData('users')->contains('id', $this->superadmin->id));

        // Tapis superadmin
        $superadminResponse = $this->actingAs($this->superadmin)
            ->get(route('superadmin.users.index', ['status' => 'superadmin']));
        $superadminResponse->assertOk();
        $this->assertTrue($superadminResponse->viewData('users')->contains('id', $this->superadmin->id));
        $this->assertFalse($superadminResponse->viewData('users')->contains('id', $this->targetUser->id));
    }
}
