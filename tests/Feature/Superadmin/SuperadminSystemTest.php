<?php

namespace Tests\Feature\Superadmin;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected Org $superadminOrg;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name' => 'Maui Sabily',
            'email' => 'admin@sabily.info',
            'password' => bcrypt('password123'),
            'is_superadmin' => true,
            'is_demo' => false,
            'status' => 'active',
        ]);

        $this->superadminOrg = Org::create([
            'name' => 'Sabily Enterprise',
            'slug' => 'sabily-enterprise',
        ]);

        OrgMember::create([
            'org_id' => $this->superadminOrg->id,
            'user_id' => $this->superadmin->id,
            'role' => 'owner',
        ]);
    }

    /**
     * Superadmin can view server health and settings page.
     */
    public function test_superadmin_can_view_system_settings_page(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->withSession(['current_org_id' => $this->superadminOrg->id])
            ->get(route('superadmin.system.index'));

        $response->assertStatus(200);
        $response->assertSee('Kesihatan Pelayan');
        $response->assertSee('Konfigurasi Self-Hosted');
        $response->assertSee('PHP');
        $response->assertSee('Laravel');
        $response->assertSee('PostgreSQL');
        $response->assertSee('registration_enabled');
    }

    /**
     * Superadmin can toggle registration_enabled setting via AJAX JSON.
     */
    public function test_superadmin_can_toggle_registration_setting(): void
    {
        // 1. Disable registration
        $response = $this->actingAs($this->superadmin)
            ->withSession(['current_org_id' => $this->superadminOrg->id])
            ->postJson(route('superadmin.system.settings.update'), [
                'registration_enabled' => false,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'settings' => [
                    'registration_enabled' => false,
                ],
            ]);

        $this->assertFalse(SystemSetting::get('registration_enabled'));
    }

    /**
     * When registration is disabled, public register is blocked with an error.
     */
    public function test_registration_is_blocked_when_setting_is_disabled(): void
    {
        SystemSetting::set('registration_enabled', false);

        $registerAttempt = $this->post('/register', [
            'name' => 'Calon Tertolak',
            'company_name' => 'Syarikat Gagal',
            'email' => 'gagal@syarikat.my',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerAttempt->assertSessionHasErrors([
            'email' => 'Pendaftaran akaun baharu ditutup oleh pentadbir sistem.',
        ]);
    }

    /**
     * Superadmin can trigger artisan optimize:clear cache clear.
     */
    public function test_superadmin_can_clear_system_cache(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->withSession(['current_org_id' => $this->superadminOrg->id])
            ->postJson(route('superadmin.system.cache.clear'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * CLI command openkuasa:make-superadmin promotes a user.
     */
    public function test_cli_make_superadmin_command(): void
    {
        $user = User::create([
            'name' => 'Harun Din',
            'email' => 'harun@example.com',
            'password' => bcrypt('password123'),
            'is_superadmin' => false,
        ]);

        $this->artisan('openkuasa:make-superadmin', ['email' => 'harun@example.com'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_superadmin);
    }

    /**
     * CLI command openkuasa:status outputs health and metrics.
     */
    public function test_cli_status_command(): void
    {
        $this->artisan('openkuasa:status')
            ->assertSuccessful()
            ->expectsOutputToContain('OPENKUASA OS - STATUS PELAYAN')
            ->expectsOutputToContain('Versi PHP')
            ->expectsOutputToContain('admin@sabily.info');
    }
}
