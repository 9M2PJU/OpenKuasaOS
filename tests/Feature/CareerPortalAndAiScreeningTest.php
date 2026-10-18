<?php

namespace Tests\Feature;

use App\Models\HireApplication;
use App\Models\HireCandidate;
use App\Models\HireJob;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Hire\AiResumeScreeningService;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerPortalAndAiScreeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Org $org;
    protected HireJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::create([
            'name' => 'HR Manager Siti',
            'email' => 'hr@pahlawan.my',
            'password' => bcrypt('password123'),
        ]);

        $this->org = Org::create([
            'name' => 'Pahlawan Digital Sdn Bhd',
            'slug' => 'pahlawan-digital',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);

        $this->job = HireJob::create([
            'org_id' => $this->org->id,
            'title' => 'Pengaturcara Perisian Kanan (Laravel)',
            'department' => 'Teknologi Maklumat',
            'employment_type' => 'full_time',
            'description' => 'Membangunkan modul teras OpenKuasaOS dengan Laravel 13, PostgreSQL dan Alpine.js.',
            'requirements' => 'Mahir PHP, Laravel, Tailwind CSS, PostgreSQL dan pengalaman membina sistem multi-tenant.',
            'salary_min' => 4500.00,
            'salary_max' => 7500.00,
            'status' => 'published',
        ]);
    }

    public function test_job_ad_generation_includes_public_career_apply_url(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/reach/generate-copy', [
            'type' => 'job',
            'job_id' => $this->job->id,
            'platform' => 'whatsapp',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'type' => 'job',
            'item_name' => $this->job->title,
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['apply_url']);
        $this->assertStringContainsString('careers/', $data['apply_url']);
        $this->assertStringContainsString($data['apply_url'], $data['body']);
        $this->assertStringContainsString('Pautan Permohonan Pantas', $data['body']);
    }

    public function test_public_career_page_renders_job_details_and_form(): void
    {
        // Public guest access
        $response = $this->get('/careers/' . $this->job->slug);

        $response->assertOk();
        $response->assertSee($this->job->title);
        $response->assertSee('Pahlawan Digital Sdn Bhd');
        $response->assertSee('RM 4,500 - RM 7,500');
        $response->assertSee('Hantar Permohonan Anda Sekarang');
        $response->assertSee('Nama Penuh (seperti dalam MyKad)');
        $response->assertSee('Tarikh Lahir');
        $response->assertSee('Alamat Kediaman Lengkap');
        $response->assertSee('Muat Naik Fail Resume / CV');
    }

    public function test_candidate_can_submit_application_with_resume_and_triggers_ai_screening(): void
    {
        $fakeResume = UploadedFile::fake()->create('resume_kamal.pdf', 250, 'application/pdf');

        $response = $this->post('/careers/' . $this->job->slug . '/apply', [
            'candidate_name' => 'Kamal Ariff bin Shamsul',
            'birth_date' => '1998-05-15',
            'email' => 'kamal.ariff@example.com',
            'phone' => '012-9988776',
            'address' => 'No 45, Jalan Anggerik 2, 47301 Petaling Jaya, Selangor',
            'cover_letter' => 'Saya mempunyai pengalaman 4 tahun dalam ekosistem PHP dan Laravel, berminat menyertai pasukan.',
            'cv_file' => $fakeResume,
        ]);

        $response->assertOk();
        $response->assertSee('Permohonan Diterima HR');
        $response->assertSee('Kamal Ariff bin Shamsul');
        $response->assertSee('Saringan AI Selesai Disimpan');

        // Verify Candidate created in DB
        $this->assertDatabaseHas('hire_candidates', [
            'org_id' => $this->org->id,
            'email' => 'kamal.ariff@example.com',
            'name' => 'Kamal Ariff bin Shamsul',
            'phone' => '012-9988776',
        ]);

        // Verify Application created & AI evaluated in DB
        $application = HireApplication::where('email', 'kamal.ariff@example.com')->first();
        $this->assertNotNull($application);
        $this->assertEquals('Kamal Ariff bin Shamsul', $application->candidate_name);
        $this->assertNotNull($application->birth_date);
        $this->assertEquals('1998-05-15', $application->birth_date->format('Y-m-d'));
        $this->assertNotNull($application->address);
        $this->assertNotNull($application->cv_file_url);
        $this->assertNotNull($application->ai_score);
        $this->assertContains($application->ai_status, ['layak', 'pertimbangan', 'tidak_layak']);
        $this->assertIsArray($application->ai_analysis);
        $this->assertNotEmpty($application->ai_analysis['summary']);
        $this->assertNotEmpty($application->ai_analysis['strengths']);
        $this->assertNotNull($application->ai_reviewed_at);
    }

    public function test_ai_screening_engine_evaluates_skills_and_generates_interview_questions(): void
    {
        $application = HireApplication::create([
            'org_id' => $this->org->id,
            'job_id' => $this->job->id,
            'candidate_name' => 'Ahmad Faiz',
            'email' => 'ahmad.faiz@example.com',
            'phone' => '013-1234567',
            'birth_date' => '1995-08-20',
            'address' => 'Kuala Lumpur, Malaysia',
            'cover_letter' => 'Mempunyai ijazah sains komputer dan berpengalaman 5 tahun dengan Laravel, PostgreSQL dan REST API.',
            'stage' => 'applied',
            'status' => 'active',
        ]);

        $service = app(AiResumeScreeningService::class);
        $result = $service->screen($application);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('strengths', $result);
        $this->assertArrayHasKey('gaps', $result);
        $this->assertArrayHasKey('interview_questions', $result);

        $application->refresh();
        $this->assertNotNull($application->ai_score);
        $this->assertEquals($result['status'], $application->ai_status);
        $this->assertNotEmpty($application->ai_analysis['interview_questions']);
    }

    public function test_hr_user_can_view_applications_and_fetch_ai_analysis(): void
    {
        $this->actingAs($this->user);

        $application = HireApplication::create([
            'org_id' => $this->org->id,
            'job_id' => $this->job->id,
            'candidate_name' => 'Nurul Huda',
            'email' => 'nurul.huda@example.com',
            'phone' => '017-9876543',
            'birth_date' => '1997-03-10',
            'address' => 'Shah Alam, Selangor',
            'stage' => 'screening',
            'status' => 'active',
            'ai_score' => 86,
            'ai_status' => 'layak',
            'ai_analysis' => [
                'summary' => 'Calon sangat layak dengan kepakaran Laravel.',
                'strengths' => ['Pengalaman PHP & Laravel', 'Latar belakang ijazah IT'],
                'gaps' => ['Perlu pengesahan kecekapan PostgreSQL lanjutan'],
                'recommendation' => 'Disyorkan untuk Temuduga Teknikal',
                'interview_questions' => ['Apakah seni bina microservices yang pernah anda laksana?'],
            ],
            'ai_reviewed_at' => now(),
        ]);

        // 1. View applications index
        $indexResponse = $this->get('/hire/applications');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Nurul Huda');
        $indexResponse->assertSee('86% Layak');

        // 2. Fetch AI analysis JSON endpoint
        $analysisResponse = $this->getJson('/hire/applications/' . $application->id . '/ai-analysis');
        $analysisResponse->assertOk();
        $analysisResponse->assertJson([
            'success' => true,
            'application' => [
                'id' => $application->id,
                'candidate_name' => 'Nurul Huda',
                'ai_score' => 86,
                'ai_status' => 'layak',
                'ai_status_label' => 'Layak',
            ],
        ]);
    }

    public function test_hr_user_can_trigger_ai_rescreening(): void
    {
        $this->actingAs($this->user);

        $application = HireApplication::create([
            'org_id' => $this->org->id,
            'job_id' => $this->job->id,
            'candidate_name' => 'Farid Iskandar',
            'email' => 'farid@example.com',
            'phone' => '011-8899776',
            'birth_date' => '1999-11-25',
            'address' => 'Cyberjaya, Selangor',
            'stage' => 'applied',
            'status' => 'active',
        ]);

        $response = $this->postJson('/hire/applications/' . $application->id . '/ai-screen');
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $application->refresh();
        $this->assertNotNull($application->ai_score);
        $this->assertNotNull($application->ai_status);
        $this->assertNotNull($application->ai_reviewed_at);
    }

    public function test_candidate_can_upload_resume_upfront_and_ai_extracts_form_data(): void
    {
        $resumeContent = "CURRICULUM VITAE\n" .
            "Nama: Muhammad Zikri bin Azman\n" .
            "Tarikh Lahir: 1996-09-12\n" .
            "Emel: zikri.azman@gmail.com\n" .
            "Telefon: 019-8765432\n" .
            "Alamat: No 28, Jalan Melati 3, 53300 Setapak, Kuala Lumpur\n\n" .
            "Ringkasan Profesional:\n" .
            "Pengaturcara Web dengan 4 tahun pengalaman membangunkan aplikasi Laravel dan pangkalan data PostgreSQL.";

        $fakeResume = UploadedFile::fake()->createWithContent('resume_zikri.txt', $resumeContent);

        $response = $this->postJson('/careers/' . $this->job->slug . '/parse-resume', [
            'cv_file' => $fakeResume,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['file_url']);
        $this->assertStringStartsWith('/storage/resumes/', $json['file_url']);
        $this->assertEquals('resume_zikri.txt', $json['file_name']);

        $data = $json['data'];
        $this->assertEquals('Muhammad Zikri bin Azman', $data['candidate_name']);
        $this->assertEquals('zikri.azman@gmail.com', $data['email']);
        $this->assertEquals('019-8765432', $data['phone']);
        $this->assertEquals('1996-09-12', $data['birth_date']);
        $this->assertStringContainsString('Setapak, Kuala Lumpur', $data['address']);
    }

    public function test_candidate_can_apply_using_pre_uploaded_resume_url_without_reuploading(): void
    {
        $response = $this->post('/careers/' . $this->job->slug . '/apply', [
            'candidate_name' => 'Muhammad Zikri bin Azman',
            'birth_date' => '1996-09-12',
            'email' => 'zikri.azman@gmail.com',
            'phone' => '019-8765432',
            'address' => 'No 28, Jalan Melati 3, 53300 Setapak, Kuala Lumpur',
            'cover_letter' => 'Saya telah menyunting maklumat ini sebelum hantar.',
            'uploaded_cv_url' => '/storage/resumes/fake_pre_uploaded_resume.pdf',
        ]);

        $response->assertOk();
        $response->assertSee('Permohonan Diterima HR');
        $response->assertSee('Muhammad Zikri bin Azman');

        $this->assertDatabaseHas('hire_applications', [
            'job_id' => $this->job->id,
            'email' => 'zikri.azman@gmail.com',
            'candidate_name' => 'Muhammad Zikri bin Azman',
            'cv_file_url' => '/storage/resumes/fake_pre_uploaded_resume.pdf',
        ]);
    }

    public function test_public_career_page_displays_step1_resume_upload_and_step2_editable_form(): void
    {
        $response = $this->get('/careers/' . $this->job->slug);

        $response->assertOk();
        $response->assertSee('Langkah 1: Muat Naik Fail Resume / CV Anda Dahulu');
        $response->assertSee('AI Auto-Isi');
        $response->assertSee('Pilih &amp; Muat Naik Resume Sekarang', false);
        $response->assertSee('Langkah 2: Semak &amp; Lengkapkan Maklumat Calon (Boleh Disunting)', false);
    }
}
