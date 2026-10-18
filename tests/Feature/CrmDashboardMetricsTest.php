<?php

namespace Tests\Feature;

use App\Models\AgentConfig;
use App\Models\AgentRun;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Database\Seeders\KasturiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmDashboardMetricsTest extends TestCase
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

    public function test_crm_dashboard_renders_metrics_and_charts_from_database_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        // Run KasturiDemoSeeder to populate DB
        $this->seed(KasturiDemoSeeder::class);

        $response = $this->get('/crm');
        $response->assertOk();

        // 1. Top 4 KPI Cards
        $response->assertSee('Active agents');
        $response->assertSee('5 / 6');
        $response->assertSee('+1');

        $response->assertSee('Tasks automated');
        $response->assertSee('1,590');
        $response->assertSee('+22%');

        $response->assertSee('Hours saved');
        $response->assertSee('48h');
        $response->assertSee('+11%');

        $response->assertSee('Avg success rate');
        $response->assertSee('93%');
        $response->assertSee('+2pt');

        // 2. Middle Row: Automated vs manual & Automation coverage
        $response->assertSee('Automated vs manual');
        $response->assertSee('Tasks handled &middot; last 8 weeks', false);
        $response->assertSee('automatedVsManualChart');
        $response->assertSee('Automation coverage');
        $response->assertSee('Pipeline on autopilot');
        $response->assertSee('82%');
        $response->assertSee('covered');

        // 3. Bottom Row: Tasks by agent & Agent strengths
        $response->assertSee('Tasks by agent');
        $response->assertSee('Completed this month');
        $response->assertSee('Deal Scorer');
        $response->assertSee('Follow-up Writer');
        $response->assertSee('Churn Predictor');
        $response->assertSee('Meeting Summarizer');
        $response->assertSee('Pipeline Forecaster');
        $response->assertSee('Data Enricher');

        $response->assertSee('Agent strengths');
        $response->assertSee('Speed &middot; accuracy &middot; volume', false);
        $response->assertSee('agentStrengthsChart');

        // 4. CRM Pipeline from DB
        $response->assertSee('Saluran Transaksi Jualan (CRM Pipeline)');
        $response->assertSee('Nilai Saluran Jualan Semasa');
        $response->assertSee('Jumlah Jualan Berjaya Ditutup (Won)');
    }

    public function test_crm_dashboard_dynamically_reflects_database_changes(): void
    {
        $this->actingAs($this->user);

        $this->seed(KasturiDemoSeeder::class);

        // Verify initial count
        $initialRuns = AgentRun::where('org_id', $this->org->id)->where('status', 'completed')->count();
        $this->assertEquals(1590, $initialRuns);

        // Add 10 new completed runs to database
        $dealScorer = AgentConfig::where('org_id', $this->org->id)->where('agent_name', 'Deal Scorer')->first();
        for ($i = 0; $i < 10; $i++) {
            AgentRun::create([
                'org_id' => $this->org->id,
                'agent_config_id' => $dealScorer->id,
                'prompt' => 'Ujian pantas skor deal ' . $i,
                'output' => 'Hasil skor deal',
                'status' => 'completed',
                'tokens_used' => 200,
            ]);
        }

        // Dashboard should now show 1,600 tasks automated
        $response = $this->get('/crm');
        $response->assertOk();
        $response->assertSee('1,600');
    }
}
