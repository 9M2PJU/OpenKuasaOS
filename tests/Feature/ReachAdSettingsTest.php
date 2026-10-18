<?php

namespace Tests\Feature;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReachAdSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Ad Manager User',
            'email' => 'admanager@syarikat.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Syarikat Maju Pemasaran Sdn Bhd',
            'slug' => 'syarikat-maju-pemasaran',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);
    }

    public function test_ad_settings_page_requires_authentication(): void
    {
        $response = $this->get('/reach/ad-settings');
        $response->assertRedirect('/login');
    }

    public function test_ad_settings_page_renders_all_sections_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/reach/ad-settings');
        $response->assertOk();

        // 1. Header & Breadcrumb
        $response->assertSee('Tetapan Iklan (Ad Settings)');
        $response->assertSee('Ad Settings &bull; Sambungan Pembekal Iklan', false);

        // 2. Card 1: Connected account & Providers
        $response->assertSee('Connected account');
        $response->assertSee('Meta Business');
        $response->assertSee('Google Ads');
        $response->assertSee('TikTok Ads Manager');
        $response->assertSee('WhatsApp Cloud API');

        // 3. Card 2: Budget & spend
        $response->assertSee('Budget & spend', false);
        $response->assertSee('Daily budget cap');
        $response->assertSee('Monthly budget cap');
        $response->assertSee('Currency');
        $response->assertSee('MYR (Ringgit Malaysia)');

        // 4. Card 3: Automation
        $response->assertSee('Automation');
        $response->assertSee('Pause ads below CTR threshold');
        $response->assertSee('Auto-optimise budget to top performers');
        $response->assertSee('Stop spend at daily budget cap');

        // 5. Card 4: Notifications
        $response->assertSee('Notifications');
        $response->assertSee('Alert me on budget overspend');
        $response->assertSee('Email me a weekly performance summary');

        // 6. Card 5: Health check
        $response->assertSee('Health check');
        $response->assertSee('Meta account connected');
        $response->assertSee('Facebook Page linked');
        $response->assertSee('Lead forms configured');
        $response->assertSee('WhatsApp number verified');
        $response->assertSee('Billing method on file');
    }

    public function test_ad_settings_can_be_updated_successfully(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/reach/ad-settings', [
            'daily_budget_cap' => 250,
            'monthly_budget_cap' => 5000,
            'currency' => 'MYR',
            'pause_low_ctr' => '1',
            'auto_optimize_budget' => '1',
            'stop_daily_cap' => '1',
            'alert_budget_overspend' => '1',
            'weekly_summary_email' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $viewResponse = $this->get('/reach/ad-settings');
        $viewResponse->assertOk();
        $viewResponse->assertSee('value="250"', false);
        $viewResponse->assertSee('value="5000"', false);
    }

    public function test_ad_provider_connect_and_disconnect_lifecycle(): void
    {
        $this->actingAs($this->user);

        // Connect Google Ads
        $connectResponse = $this->post('/reach/ad-settings/connect', [
            'provider' => 'google',
            'customer_id' => '111-222-3333',
            'conversion_tag_id' => 'AW-111222333',
        ]);

        $connectResponse->assertRedirect();
        $connectResponse->assertSessionHas('success');

        $viewResponse = $this->get('/reach/ad-settings');
        $viewResponse->assertOk();
        $viewResponse->assertSee('111-222-3333');
        $viewResponse->assertSee('AW-111222333');

        // Disconnect Google Ads
        $disconnectResponse = $this->post('/reach/ad-settings/disconnect', [
            'provider' => 'google',
        ]);

        $disconnectResponse->assertRedirect();
        $disconnectResponse->assertSessionHas('success');
    }
}
