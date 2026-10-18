<?php

namespace Tests\Feature;

use App\Models\HireApplication;
use App\Models\HireCandidate;
use App\Models\HireInterview;
use App\Models\HireJob;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HireInterviewManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;
    protected HireJob $job;
    protected HireApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'HR Manager Haziq',
            'email' => 'hr.haziq@pahlawan.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Lekir Technologies Sdn Bhd',
            'slug' => 'lekir-tech',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'admin',
        ]);

        OrganizationContext::set($this->org);

        $this->job = HireJob::create([
            'org_id' => $this->org->id,
            'title' => 'Jurutera DevOps Kanan',
            'department' => 'Infrastruktur',
            'employment_type' => 'full_time',
            'description' => 'Menguruskan sistem awan dan automasi.',
            'status' => 'published',
        ]);

        $candidate = HireCandidate::create([
            'org_id' => $this->org->id,
            'name' => 'Nur Asmawi bin Subri',
            'email' => 'asmawi@example.com',
            'phone' => '011-11458944',
        ]);

        $this->application = HireApplication::create([
            'org_id' => $this->org->id,
            'job_id' => $this->job->id,
            'candidate_id' => $candidate->id,
            'candidate_name' => 'Nur Asmawi bin Subri',
            'email' => 'asmawi@example.com',
            'phone' => '011-11458944',
            'stage' => 'screening',
            'status' => 'active',
        ]);
    }

    public function test_hr_user_can_view_interviews_list_with_action_buttons(): void
    {
        $this->actingAs($this->user);

        $interview = HireInterview::create([
            'org_id' => $this->org->id,
            'application_id' => $this->application->id,
            'scheduled_at' => now()->addDays(2),
            'interviewer_name' => 'Dr. Kamarulzaman & Pn. Aisyah',
            'location_or_link' => 'https://meet.google.com/abc-defg-hij',
            'status' => 'scheduled',
        ]);

        $response = $this->get('/hire/interviews');

        $response->assertOk();
        $response->assertSee('Jadual Sesi Temu Duga');
        $response->assertSee('Nur Asmawi bin Subri');
        $response->assertSee('Dr. Kamarulzaman & Pn. Aisyah');
        $response->assertSee('Pautan Sesi');
        $response->assertSee('Sunting');
        $response->assertSee('Padam');
    }

    public function test_scheduling_interview_stores_data_and_advances_application_stage(): void
    {
        $this->actingAs($this->user);

        $scheduledTime = now()->addDays(3)->setHour(10)->setMinute(0)->setSecond(0);

        $response = $this->post('/hire/interviews', [
            'application_id' => $this->application->id,
            'scheduled_at' => $scheduledTime->format('Y-m-d H:i:s'),
            'interviewer_name' => 'En. Razak & Pn. Faridah',
            'location_or_link' => 'Bilik Mesyuarat Melati 1',
            'notes' => 'Sila bawa salinan sijil kelayakan asal.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('hire_interviews', [
            'org_id' => $this->org->id,
            'application_id' => $this->application->id,
            'interviewer_name' => 'En. Razak & Pn. Faridah',
            'location_or_link' => 'Bilik Mesyuarat Melati 1',
            'notes' => 'Sila bawa salinan sijil kelayakan asal.',
            'status' => 'scheduled',
        ]);

        $this->application->refresh();
        $this->assertEquals('interview', $this->application->stage);
    }

    public function test_scheduling_duplicate_interview_is_prevented_on_backend(): void
    {
        $this->actingAs($this->user);

        $scheduledTime = '2026-10-25 14:30:00';

        // 1. First submission
        $response1 = $this->post('/hire/interviews', [
            'application_id' => $this->application->id,
            'scheduled_at' => $scheduledTime,
            'interviewer_name' => 'En. Razak',
            'location_or_link' => 'Pejabat Utama',
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        // 2. Second rapid submission (simulating double click)
        $response2 = $this->post('/hire/interviews', [
            'application_id' => $this->application->id,
            'scheduled_at' => $scheduledTime,
            'interviewer_name' => 'En. Razak',
            'location_or_link' => 'Pejabat Utama',
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('info');

        // Verify only 1 record exists in DB
        $count = HireInterview::where('application_id', $this->application->id)
            ->where('scheduled_at', $scheduledTime)
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_hr_user_can_update_interview_details(): void
    {
        $this->actingAs($this->user);

        $interview = HireInterview::create([
            'org_id' => $this->org->id,
            'application_id' => $this->application->id,
            'scheduled_at' => now()->addDays(2),
            'interviewer_name' => 'Panel Lama',
            'location_or_link' => 'Bilik 1',
            'status' => 'scheduled',
        ]);

        $newTime = now()->addDays(4)->format('Y-m-d H:i:s');

        $response = $this->put('/hire/interviews/' . $interview->id, [
            'scheduled_at' => $newTime,
            'interviewer_name' => 'Panel Baru (Dr. Syahirah & Tn. Azman)',
            'location_or_link' => 'https://meet.google.com/updated-link',
            'status' => 'completed',
            'notes' => 'Temu duga selesai dengan cemerlang.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $interview->refresh();
        $this->assertEquals('Panel Baru (Dr. Syahirah & Tn. Azman)', $interview->interviewer_name);
        $this->assertEquals('https://meet.google.com/updated-link', $interview->location_or_link);
        $this->assertEquals('completed', $interview->status);
        $this->assertEquals('Temu duga selesai dengan cemerlang.', $interview->notes);
    }

    public function test_hr_user_can_delete_interview_entry(): void
    {
        $this->actingAs($this->user);

        $interview = HireInterview::create([
            'org_id' => $this->org->id,
            'application_id' => $this->application->id,
            'scheduled_at' => now()->addDays(1),
            'interviewer_name' => 'Panel A',
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('hire_interviews', [
            'id' => $interview->id,
        ]);

        $response = $this->delete('/hire/interviews/' . $interview->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('hire_interviews', [
            'id' => $interview->id,
        ]);
    }
}
