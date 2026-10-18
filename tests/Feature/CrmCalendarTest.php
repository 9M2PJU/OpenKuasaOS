<?php

namespace Tests\Feature;

use App\Models\CrmAppointment;
use App\Models\CrmContact;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Database\Seeders\KasturiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmCalendarTest extends TestCase
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

    public function test_calendar_page_requires_authentication(): void
    {
        $response = $this->get('/crm/calendar');
        $response->assertRedirect('/login');
    }

    public function test_calendar_page_renders_kpi_cards_and_appointments_matching_screenshot(): void
    {
        $this->actingAs($this->user);

        // Seed appointments
        $this->seed(KasturiDemoSeeder::class);

        $response = $this->get('/crm/calendar');
        $response->assertOk();

        // 1. Top 4 KPI Cards
        $response->assertSee('Today');
        $response->assertSee('Next 7 days');
        $response->assertSee('In October 2026');
        $response->assertSee('Next 30 days');

        // 2. Calendar Header & Days of Week
        $response->assertSee('October 2026');
        $response->assertSee('Sun');
        $response->assertSee('Mon');
        $response->assertSee('Tue');
        $response->assertSee('Wed');
        $response->assertSee('Thu');
        $response->assertSee('Fri');
        $response->assertSee('Sat');

        // 3. Upcoming Appointments & Legend matching screenshot
        $response->assertSee('Upcoming');
        $response->assertSee('Next 30 days');
        $response->assertSee('Aisyah Rahim');
        $response->assertSee('Discovery call');
        $response->assertSee('Faiz Hakim');
        $response->assertSee('Product demo');
        $response->assertSee('Nurul Huda');
        $response->assertSee('Follow-up');

        // 4. Legend
        $response->assertSee('Appointment');
        $response->assertSee('Deal closing');
    }

    public function test_can_create_new_appointment(): void
    {
        $this->actingAs($this->user);

        $contact = CrmContact::create([
            'org_id' => $this->org->id,
            'name' => 'Kamarul Ariffin',
            'email' => 'kamarul@perintis.my',
            'company' => 'Perintis Media',
        ]);

        $response = $this->post('/crm/appointments', [
            'title' => 'Sesi Konsultasi Sistem',
            'contact_name' => 'Kamarul Ariffin',
            'contact_id' => $contact->id,
            'type' => 'appointment',
            'scheduled_date' => '2026-10-20',
            'scheduled_time' => '14:30',
            'duration_minutes' => 60,
            'location' => 'Google Meet',
            'notes' => 'Perbincangan pakej ERP.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('crm_appointments', [
            'org_id' => $this->org->id,
            'title' => 'Sesi Konsultasi Sistem',
            'contact_name' => 'Kamarul Ariffin',
            'type' => 'appointment',
            'location' => 'Google Meet',
        ]);
    }

    public function test_can_update_appointment_status_and_delete(): void
    {
        $this->actingAs($this->user);

        $appt = CrmAppointment::create([
            'org_id' => $this->org->id,
            'title' => 'Sesi Ujian Demo',
            'contact_name' => 'Ali Imran',
            'type' => 'follow_up',
            'scheduled_at' => '2026-10-22 15:00:00',
            'status' => 'scheduled',
        ]);

        // Update status
        $statusResponse = $this->post("/crm/appointments/{$appt->id}/status", [
            'status' => 'completed',
        ]);
        $statusResponse->assertRedirect();
        $statusResponse->assertSessionHas('success');

        $appt->refresh();
        $this->assertEquals('completed', $appt->status);

        // Delete appointment
        $deleteResponse = $this->delete("/crm/appointments/{$appt->id}");
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('crm_appointments', [
            'id' => $appt->id,
        ]);
    }
}
