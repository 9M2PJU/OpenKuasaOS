<?php

namespace Tests\Feature;

use App\Models\FinanceInvoice;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Organization A cannot access or see invoices from Organization B.
     */
    public function test_organization_a_cannot_access_organization_b_invoices(): void
    {
        // 1. Create Organization A and User A
        $orgA = Org::create([
            'name' => 'Syarikat Megah Holdings',
            'slug' => 'megah-holdings',
        ]);

        $userA = User::create([
            'name' => 'Ali Megah',
            'email' => 'ali@megah.com',
            'password' => bcrypt('password123'),
        ]);

        OrgMember::create([
            'org_id' => $orgA->id,
            'user_id' => $userA->id,
            'role' => 'owner',
        ]);

        // 2. Create Organization B and User B
        $orgB = Org::create([
            'name' => 'Nusantara Logistics Sdn Bhd',
            'slug' => 'nusantara-logistics',
        ]);

        $userB = User::create([
            'name' => 'Badri Nusantara',
            'email' => 'badri@nusantara.com',
            'password' => bcrypt('password123'),
        ]);

        OrgMember::create([
            'org_id' => $orgB->id,
            'user_id' => $userB->id,
            'role' => 'owner',
        ]);

        // 3. Create invoice belonging to Org A
        $invoiceA = FinanceInvoice::create([
            'org_id' => $orgA->id,
            'customer_name' => 'Pelanggan Rahsia Org A',
            'invoice_number' => 'INV-A-001',
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
        ]);

        // 4. Authenticate as User B (scoped to Org B)
        Sanctum::actingAs($userB);

        // Attempt to GET invoices list as User B
        $responseList = $this->withHeaders(['X-Organization-Id' => $orgB->id])
            ->getJson('/api/v1/finance/invoices');

        $responseList->assertStatus(200);
        $data = $responseList->json('data');

        // Org B must see 0 invoices, invoiceA must NOT be leaked
        $this->assertCount(0, $data);

        // Attempt to directly GET Org A's invoice as User B
        $responseShow = $this->withHeaders(['X-Organization-Id' => $orgB->id])
            ->getJson("/api/v1/finance/invoices/{$invoiceA->id}");

        // Tenant scope ensures it behaves as Not Found (404) for Org B
        $responseShow->assertStatus(404);

        // 5. Attempt to forge X-Organization-Id to Org A while authenticated as User B
        $responseForged = $this->withHeaders(['X-Organization-Id' => $orgA->id])
            ->getJson("/api/v1/finance/invoices/{$invoiceA->id}");

        // Must return 403 Forbidden because User B is not a member of Org A
        $responseForged->assertStatus(403);
        $responseForged->assertJson([
            'success' => false,
            'error' => [
                'code' => 'ORGANIZATION_FORBIDDEN',
            ]
        ]);
    }
}
