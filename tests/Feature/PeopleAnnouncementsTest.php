<?php

namespace Tests\Feature;

use App\Models\HrAnnouncement;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Database\Seeders\LekiuDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        // Fix system test time to 2026-10-14 matching current context
        Carbon::setTestNow('2026-10-14 10:00:00');

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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_announcements_page_renders_with_metrics_and_categories_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        // Seed Lekiu demo dataset
        $this->seed(LekiuDemoSeeder::class);

        $response = $this->get('/people/announcements');
        $response->assertOk();

        // 1. Header & Title
        $response->assertSee('Announcements');
        $response->assertSee('Company-wide updates for your team.');

        // 2. KPI Cards
        $response->assertSee('1 draft not counted');
        $response->assertSee('This month');
        $response->assertSee('Categories');

        // 3. By Category Chart
        $response->assertSee('By category');
        $response->assertSee('Across all published announcements');
        $response->assertSee('posts');
        $response->assertSee('General');
        $response->assertSee('Holiday');
        $response->assertSee('Benefits');
        $response->assertSee('Strategy');
        $response->assertSee('Policy');

        // 4. Latest Announcements List
        $response->assertSee('Latest announcements');
        $response->assertSee('Drafts first, then newest');
        $response->assertSee('Year-end dinner');
        $response->assertSee('Office closed for National Day');
        $response->assertSee('New panel clinics added');
        $response->assertSee('Quarterly town hall');
        $response->assertSee('Updated leave policy');
        $response->assertSee('Second-half priorities');

        // 5. Past Date announcement locking
        $response->assertSee('Telah Lepas Tarikh (Kunci)');
    }

    public function test_user_can_create_new_announcement(): void
    {
        $this->actingAs($this->user);

        $payload = [
            'title' => 'Bonus Raya & Penghargaan 2026',
            'content' => 'Prestasi syarikat membanggakan, pengurusan bersetuju memberikan ganjaran.',
            'category' => 'benefits',
            'status' => 'published',
            'effective_date' => '2026-10-20',
        ];

        $response = $this->post('/people/announcements', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengumuman berjaya dicipta.');

        $this->assertDatabaseHas('hr_announcements', [
            'org_id' => $this->org->id,
            'title' => 'Bonus Raya & Penghargaan 2026',
            'category' => 'benefits',
            'status' => 'published',
        ]);
    }

    public function test_user_can_update_announcement_with_future_date(): void
    {
        $this->actingAs($this->user);

        $announcement = HrAnnouncement::create([
            'org_id' => $this->org->id,
            'author_id' => $this->user->id,
            'author_name' => 'Ahmad Zulkifli',
            'title' => 'Sesi Townhall Masa Depan',
            'content' => 'Sesi berkongsi hala tuju.',
            'category' => 'general',
            'status' => 'published',
            'effective_date' => '2026-10-25',
        ]);

        $payload = [
            'title' => 'Sesi Townhall Masa Depan (Dikemaskini)',
            'content' => 'Masa diubah ke jam 4 petang.',
            'category' => 'general',
            'status' => 'published',
            'effective_date' => '2026-10-25',
        ];

        $response = $this->put("/people/announcements/{$announcement->id}", $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengumuman berjaya dikemaskini.');

        $this->assertDatabaseHas('hr_announcements', [
            'id' => $announcement->id,
            'title' => 'Sesi Townhall Masa Depan (Dikemaskini)',
            'content' => 'Masa diubah ke jam 4 petang.',
        ]);
    }

    public function test_user_CANNOT_update_announcement_if_date_has_passed(): void
    {
        $this->actingAs($this->user);

        // Created with past date: 10 Sep 2026 (today is 14 Oct 2026)
        $pastAnnouncement = HrAnnouncement::create([
            'org_id' => $this->org->id,
            'author_id' => $this->user->id,
            'author_name' => 'Ahmad Zulkifli',
            'title' => 'Keutamaan Separuh Tahun Pertama',
            'content' => 'Dokumen rasmi suku tahun lalu.',
            'category' => 'strategy',
            'status' => 'published',
            'effective_date' => '2026-09-10',
        ]);

        $payload = [
            'title' => 'Cubaan Ubah Yang Lepas Tarikh',
            'content' => 'Ini tidak patut dibenarkan.',
            'category' => 'strategy',
            'status' => 'published',
            'effective_date' => '2026-09-10',
        ];

        $response = $this->put("/people/announcements/{$pastAnnouncement->id}", $payload);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Pengumuman ini telah melepasi tarikh dan tidak boleh disunting.');

        // Verify title did NOT change in database
        $this->assertDatabaseHas('hr_announcements', [
            'id' => $pastAnnouncement->id,
            'title' => 'Keutamaan Separuh Tahun Pertama',
        ]);
    }

    public function test_user_can_delete_announcement_with_future_date(): void
    {
        $this->actingAs($this->user);

        $announcement = HrAnnouncement::create([
            'org_id' => $this->org->id,
            'author_id' => $this->user->id,
            'author_name' => 'Ahmad Zulkifli',
            'title' => 'Program Family Day 2026',
            'content' => 'Aktiviti santai pekerja.',
            'category' => 'general',
            'status' => 'published',
            'effective_date' => '2026-11-15',
        ]);

        $response = $this->delete("/people/announcements/{$announcement->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengumuman berjaya dipadam.');

        $this->assertDatabaseMissing('hr_announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_user_CANNOT_delete_announcement_if_date_has_passed(): void
    {
        $this->actingAs($this->user);

        // Created with past date
        $pastAnnouncement = HrAnnouncement::create([
            'org_id' => $this->org->id,
            'author_id' => $this->user->id,
            'author_name' => 'Ahmad Zulkifli',
            'title' => 'Pengumuman Lapuk',
            'content' => 'Maklumat lama.',
            'category' => 'policy',
            'status' => 'published',
            'effective_date' => '2026-08-01',
        ]);

        $response = $this->delete("/people/announcements/{$pastAnnouncement->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Pengumuman ini telah melepasi tarikh dan tidak boleh dipadam.');

        // Verify record STILL exists in database
        $this->assertDatabaseHas('hr_announcements', [
            'id' => $pastAnnouncement->id,
            'title' => 'Pengumuman Lapuk',
        ]);
    }
}
