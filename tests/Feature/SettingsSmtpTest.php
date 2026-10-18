<?php

namespace Tests\Feature;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\OrgSmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SettingsSmtpTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'ceo@syarikatanda.com',
            'name' => 'Pengarah Urusan',
        ]);

        $this->org = Org::create([
            'name' => 'Waja Mega Sdn Bhd',
            'slug' => 'waja-mega',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);
    }

    public function test_settings_page_displays_smtp_section(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Konfigurasi Emel SMTP Syarikat');
        $response->assertSee('AES-256');
        $response->assertSee('Uji Sambungan Emel');
        $response->assertSee('Simpan Konfigurasi SMTP');
        $response->assertSee('companySmtpComponent()');
    }

    public function test_user_can_save_company_smtp_settings_with_secure_encryption(): void
    {
        $rawPassword = 'SuperSecret_Smtp_Password_!@#2026';

        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->post(route('settings.smtp'), [
                'is_active' => '1',
                'mail_host' => 'smtp.syarikatanda.com',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_username' => 'invois@syarikatanda.com',
                'mail_password' => $rawPassword,
                'mail_from_address' => 'invois@syarikatanda.com',
                'mail_from_name' => 'Waja Mega Billing',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        // Semak rekod wujud
        $smtp = OrgSmtpSetting::where('org_id', $this->org->id)->first();
        $this->assertNotNull($smtp);
        $this->assertTrue($smtp->is_active);
        $this->assertEquals('smtp.syarikatanda.com', $smtp->mail_host);
        $this->assertEquals(587, $smtp->mail_port);
        $this->assertEquals('tls', $smtp->mail_encryption);
        $this->assertEquals('invois@syarikatanda.com', $smtp->mail_username);
        $this->assertEquals('invois@syarikatanda.com', $smtp->mail_from_address);
        $this->assertEquals('Waja Mega Billing', $smtp->mail_from_name);

        // KESELAMATAN PANGKALAN DATA (Database Security Verification):
        // Nilai mentah dalam pangkalan data mestilah TIDAK sama dengan kata laluan asal
        $rawDbValue = DB::table('org_smtp_settings')
            ->where('org_id', $this->org->id)
            ->value('mail_password');

        $this->assertNotNull($rawDbValue);
        $this->assertNotEquals($rawPassword, $rawDbValue, 'Kata laluan mentah tidak boleh disimpan dalam teks biasa!');
        // Ciphertext Laravel adalah payload JSON yang mengandungi iv, value, mac
        $decodedCipher = json_decode(base64_decode($rawDbValue), true);
        $this->assertTrue(is_array($decodedCipher) && isset($decodedCipher['iv'], $decodedCipher['value']));

        // Apabila diakses melalui Model Eloquent yang mempunyai cast encrypted, ia didekrip automatik
        $this->assertEquals($rawPassword, $smtp->mail_password);
    }

    public function test_updating_without_password_preserves_existing_encrypted_password(): void
    {
        $existingPassword = 'Original_Encrypted_Secret_999';

        $smtp = OrgSmtpSetting::create([
            'org_id' => $this->org->id,
            'is_active' => true,
            'mail_host' => 'old.smtp.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'old@syarikat.com',
            'mail_password' => $existingPassword,
            'mail_from_address' => 'old@syarikat.com',
            'mail_from_name' => 'Old Sender',
        ]);

        // Pengguna hanya menukar hos dan membiarkan kata laluan kosong
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->post(route('settings.smtp'), [
                'is_active' => '1',
                'mail_host' => 'new.smtp.com',
                'mail_port' => 465,
                'mail_encryption' => 'ssl',
                'mail_username' => 'new@syarikat.com',
                'mail_password' => '', // Dikosongkan
                'mail_from_address' => 'new@syarikat.com',
                'mail_from_name' => 'New Sender',
            ]);

        $response->assertRedirect(route('settings.index'));

        $smtp->refresh();
        $this->assertEquals('new.smtp.com', $smtp->mail_host);
        $this->assertEquals(465, $smtp->mail_port);
        $this->assertEquals('ssl', $smtp->mail_encryption);
        // Kata laluan asal kekal selamat dan tidak terpadam
        $this->assertEquals($existingPassword, $smtp->mail_password);
    }

    public function test_smtp_password_is_hidden_from_serialization(): void
    {
        $smtp = OrgSmtpSetting::create([
            'org_id' => $this->org->id,
            'is_active' => true,
            'mail_host' => 'smtp.test.com',
            'mail_port' => 587,
            'mail_username' => 'test@test.com',
            'mail_password' => 'super_secret',
            'mail_from_address' => 'test@test.com',
        ]);

        $array = $smtp->toArray();
        $this->assertArrayNotHasKey('mail_password', $array, 'mail_password mesti disembunyikan daripada toArray()');

        $json = $smtp->toJson();
        $this->assertStringNotContainsString('super_secret', $json, 'Kata laluan tidak boleh bocor dalam JSON!');
    }

    public function test_test_smtp_endpoint_validates_inputs(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.smtp.test'), [
                'test_email' => 'invalid-email',
                'mail_host' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_test_smtp_endpoint_handles_connection_attempt(): void
    {
        // Ujian penghantaran emel percubaan
        $response = $this->actingAs($this->user)
            ->withSession(['current_org_id' => $this->org->id])
            ->postJson(route('settings.smtp.test'), [
                'test_email' => 'recipient@example.com',
                'mail_host' => '127.0.0.1',
                'mail_port' => 2525,
                'mail_username' => 'demo',
                'mail_password' => 'demo_pass',
                'mail_encryption' => 'none',
                'mail_from_address' => 'billing@syarikatanda.com',
                'mail_from_name' => 'Waja Mega Billing',
            ]);

        // Boleh sama ada 200 jika pelayan tempatan wujud, atau 422 dengan ralat sambungan terperinci
        $this->assertContains($response->status(), [200, 422]);
        $this->assertArrayHasKey('success', $response->json());
        $this->assertArrayHasKey('message', $response->json());
    }
}
