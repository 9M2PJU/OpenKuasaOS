<?php

namespace Tests\Feature;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLogoTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->org = Org::create([
            'name' => 'Wira Tech Sdn Bhd',
            'slug' => 'wira-tech',
        ]);

        $this->user = User::create([
            'name' => 'Kamarul Ariffin',
            'email' => 'kamarul@wiratech.my',
            'password' => bcrypt('password123'),
            'locale' => 'ms',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        session(['current_org_id' => $this->org->id]);
        OrganizationContext::set($this->org);
    }

    public function test_settings_page_renders_logo_upload_section_and_recommendations(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Logo Rasmi Syarikat');
        $response->assertSee('Auto-Upload & Save');
        $response->assertSee('Format Disyorkan');
        $response->assertSee('Dimensi / Nisbah Ideal');
        $response->assertSee('Had Saiz Fail');
        $response->assertSee('400 × 120 px');
        $response->assertSee('2MB');
        $response->assertSee('Pratonton Paparan Pada Kepala Invois & Sebutharga');
        $response->assertSee('Pilih & Muat Naik Logo');
        $response->assertSee('companyLogoUploader()');
    }

    private function createFakePng(string $filename = 'logo.png', int $kilobytes = 10): UploadedFile
    {
        $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        if ($kilobytes > 1) {
            $pngContent .= str_repeat(' ', ($kilobytes * 1024) - strlen($pngContent));
        }

        return UploadedFile::fake()->createWithContent($filename, $pngContent);
    }

    public function test_user_can_upload_company_logo_via_ajax(): void
    {
        $file = $this->createFakePng('company_logo.png', 50);

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.logo'), [
                'logo' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'logo_url', 'message']);
        $this->assertTrue($response->json('success'));

        $this->org->refresh();
        $this->assertNotNull($this->org->logo_url);
        $this->assertStringContainsString('logos/', $this->org->logo_url);

        $storedPath = str_replace('/storage/', '', $this->org->logo_url);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_user_can_upload_svg_logo(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60"><rect width="200" height="60" fill="#4f46e5"/></svg>';
        $file = UploadedFile::fake()->createWithContent('brand.svg', $svgContent);

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.logo'), [
                'logo' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'logo_url', 'message']);
        $this->assertTrue($response->json('success'));

        $this->org->refresh();
        $this->assertNotNull($this->org->logo_url);
        $this->assertStringContainsString('.svg', $this->org->logo_url);
    }

    public function test_upload_rejects_non_image_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.logo'), [
                'logo' => $file,
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('logo', $response->json('error.details'));
    }

    public function test_upload_rejects_file_exceeding_2mb(): void
    {
        // 2.5MB
        $file = $this->createFakePng('large_logo.png', 2600);

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.logo'), [
                'logo' => $file,
            ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('logo', $response->json('error.details'));
    }

    public function test_user_can_remove_company_logo(): void
    {
        // First upload logo
        $file = $this->createFakePng('logo.png', 20);
        $storedPath = $file->store('logos', 'public');
        $this->org->update(['logo_url' => Storage::disk('public')->url($storedPath)]);

        $this->assertNotNull($this->org->fresh()->logo_url);

        // Remove logo
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.logo.remove'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->org->refresh();
        $this->assertNull($this->org->logo_url);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_invoice_shows_company_logo_when_present(): void
    {
        $file = $this->createFakePng('my_brand.png', 20);
        $storedPath = $file->store('logos', 'public');
        $logoUrl = Storage::disk('public')->url($storedPath);
        $this->org->update(['logo_url' => $logoUrl]);

        // Create an invoice
        $invoice = \App\Models\FinanceInvoice::create([
            'org_id' => $this->org->id,
            'invoice_number' => 'INV-2026-999',
            'customer_name' => 'Pelanggan Setia Sdn Bhd',
            'customer_email' => 'setia@example.com',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
            'subtotal' => 1000.00,
            'sst_rate' => 6.00,
            'sst_amount' => 60.00,
            'total_amount' => 1060.00,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->get(route('finance.invoices.show', $invoice->id));

        $response->assertOk();
        $response->assertSee($logoUrl);
    }
}
