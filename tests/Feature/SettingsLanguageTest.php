<?php

namespace Tests\Feature;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SettingsLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::create([
            'name' => 'Nusantara Global Tech',
            'slug' => 'nusantara-tech',
        ]);

        $this->user = User::create([
            'name' => 'Farhan Razali',
            'email' => 'farhan@nusantaratech.my',
            'password' => bcrypt('password123'),
            'locale' => 'ms',
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        OrganizationContext::set($this->org);
    }

    /**
     * Test default language is Bahasa Melayu (ms).
     */
    public function test_default_language_is_bahasa_melayu(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.index'));

        $response->assertOk();
        $this->assertEquals('ms', App::getLocale());
        $response->assertSee('Tetapan Sistem');
        $response->assertSee('Pilihan Bahasa Utama');
        $response->assertSee('Bahasa Melayu');
    }

    /**
     * Test user can change default language to English (en).
     */
    public function test_user_can_switch_language_to_english(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.language'), [
                'locale' => 'en',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('locale', 'en');
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('en', $this->user->locale);

        // Follow redirect and check English interface
        $followResponse = $this->actingAs($this->user)->get(route('settings.index'));
        $followResponse->assertOk();
        $this->assertEquals('en', App::getLocale());
        $followResponse->assertSee('System Settings');
        $followResponse->assertSee('Default System Language');
    }

    /**
     * Test user can switch language back to Bahasa Melayu.
     */
    public function test_user_can_switch_language_back_to_bahasa_melayu(): void
    {
        // First set to English
        $this->actingAs($this->user)->post(route('settings.language'), ['locale' => 'en']);

        // Then switch back to Bahasa Melayu
        $response = $this->actingAs($this->user)->post(route('settings.language'), [
            'locale' => 'ms',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('locale', 'ms');

        $this->user->refresh();
        $this->assertEquals('ms', $this->user->locale);

        $followResponse = $this->actingAs($this->user)->get(route('settings.index'));
        $followResponse->assertOk();
        $this->assertEquals('ms', App::getLocale());
        $followResponse->assertSee('Tetapan Sistem');
        $followResponse->assertSee('Pilihan Bahasa Utama');
    }

    /**
     * Test unsupported locales are rejected by validation.
     */
    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('settings.index'))
            ->post(route('settings.language'), [
                'locale' => 'fr',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors('locale');
    }

    /**
     * Test user can update basic profile from settings.
     */
    public function test_user_can_update_profile_in_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.profile'), [
                'name' => 'Farhan Razali Updated',
                'email' => 'farhan.new@nusantaratech.my',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('Farhan Razali Updated', $this->user->name);
        $this->assertEquals('farhan.new@nusantaratech.my', $this->user->email);
    }

    /**
     * Test user can update company / organization name from profile settings.
     */
    public function test_user_can_update_organization_name_in_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.profile'), [
                'name' => 'Farhan Razali',
                'email' => 'farhan@nusantaratech.my',
                'org_name' => 'Nusantara Global Tech Sdn Bhd',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->org->refresh();
        $this->assertEquals('Nusantara Global Tech Sdn Bhd', $this->org->name);
    }

    /**
     * Test password change fails without current password.
     */
    public function test_password_change_fails_without_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.password'), [
                'current_password' => '',
                'password' => 'newSecretPass123',
                'password_confirmation' => 'newSecretPass123',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /**
     * Test password change fails if current password is incorrect.
     */
    public function test_password_change_fails_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.password'), [
                'current_password' => 'wrongOriginalPassword',
                'password' => 'newSecretPass123',
                'password_confirmation' => 'newSecretPass123',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /**
     * Test password change fails if new password confirmation does not match.
     */
    public function test_password_change_fails_with_mismatched_confirmation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.password'), [
                'current_password' => 'password123',
                'password' => 'newSecretPass123',
                'password_confirmation' => 'differentPass123',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test password change succeeds when valid current password is provided.
     */
    public function test_user_can_successfully_change_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('settings.password'), [
                'current_password' => 'password123',
                'password' => 'newSecurePassword456',
                'password_confirmation' => 'newSecurePassword456',
            ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        // Verify user can authenticate with the new password
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'email' => 'farhan@nusantaratech.my',
            'password' => 'newSecurePassword456',
        ]);

        $loginResponse->assertRedirect();
        $this->assertAuthenticatedAs($this->user);
    }
}
