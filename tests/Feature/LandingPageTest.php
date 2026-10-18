<?php

namespace Tests\Feature;

use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_landing_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('KerjaAI');
        $response->assertSee('https://github.com/engkuhelmy/OpenKuasaOS');
        $response->assertSee('Tuah AI');
        $response->assertSee('Jebat');
        $response->assertSee('Kasturi');
        $response->assertSee('Lekiu');
        $response->assertSee('Lekir');
        $response->assertSee('5 Modul');
        $response->assertSee('5 Modul Pahlawan KerjaAI');
        $response->assertSee('Terokai 5 Modul Pahlawan');
        $response->assertSee('Studio Iklan');
        $response->assertSee('Katalog Produk');
        $response->assertSee('Pengambilan');
        $response->assertSee('e-Invois');
        $response->assertDontSee('6 Modul');
        $response->assertDontSee('Bendahara');
        $response->assertSee('Log Masuk');
        $response->assertSee('Mula Percuma');
    }

    public function test_authenticated_user_sees_dashboard_link()
    {
        $user = User::factory()->create();
        $org = Org::create([
            'id' => (string) Str::uuid(),
            'name' => 'Syarikat Maju Jaya Sdn Bhd',
            'slug' => 'syarikat-maju-jaya',
        ]);
        OrgMember::create([
            'id' => (string) Str::uuid(),
            'org_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_org_id' => $org->id])
            ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Buka Dashboard');
    }
}
