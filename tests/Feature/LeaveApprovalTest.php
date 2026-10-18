<?php

namespace Tests\Feature;

use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;
    protected User $user;
    protected HrEmployee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::create([
            'name' => 'Lekiu HR Agency',
            'slug' => 'lekiu-hr',
        ]);

        $this->user = User::create([
            'name' => 'Admin HR',
            'email' => 'hr@lekiu.my',
            'password' => bcrypt('password123'),
        ]);

        OrgMember::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'admin',
        ]);

        OrganizationContext::set($this->org);

        $this->employee = HrEmployee::create([
            'org_id' => $this->org->id,
            'user_id' => $this->user->id,
            'employee_number' => 'EMP-001',
            'full_name' => 'Kasturi bin Jebat',
            'ic_passport_number' => '900101-14-5544',
            'position' => 'Senior Developer',
            'join_date' => '2024-01-01',
            'basic_salary' => 6500.00,
            'epf_rate' => 11.00,
            'annual_leave_balance' => 14.0,
            'medical_leave_balance' => 14.0,
            'status' => 'active',
        ]);

        Sanctum::actingAs($this->user);
    }

    /**
     * Test leave approval deducts employee annual leave quota.
     */
    public function test_leave_approval_deducts_annual_leave_quota(): void
    {
        $headers = ['X-Organization-Id' => $this->org->id];

        // 1. Submit leave request for 3 days: 2026-11-01 to 2026-11-03
        $resSubmit = $this->withHeaders($headers)->postJson('/api/v1/people/leave/requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'reason' => 'Bercuti bersama keluarga di Pulau Tioman',
        ]);

        $resSubmit->assertStatus(201);
        $leaveId = $resSubmit->json('data.id');
        $this->assertEquals(3.0, $resSubmit->json('data.days_count'));

        // Balance before approval is still 14.0
        $this->employee->refresh();
        $this->assertEquals(14.0, (float) $this->employee->annual_leave_balance);

        // 2. Approve the leave request
        $resApprove = $this->withHeaders($headers)
            ->postJson("/api/v1/people/leave/requests/{$leaveId}/approve");

        $resApprove->assertStatus(200);
        $this->assertEquals('approved', $resApprove->json('data.status'));

        // Assert 3 days were deducted from employee balance: 14.0 - 3.0 = 11.0
        $this->employee->refresh();
        $this->assertEquals(11.0, (float) $this->employee->annual_leave_balance);
    }

    /**
     * Test that date overlap rejection prevents conflicting leaves.
     */
    public function test_overlapping_leave_request_is_rejected(): void
    {
        $headers = ['X-Organization-Id' => $this->org->id];

        // Existing approved leave: 2026-12-10 to 2026-12-15
        HrLeaveRequest::create([
            'org_id' => $this->org->id,
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-15',
            'days_count' => 6.0,
            'status' => 'approved',
        ]);

        // Attempt overlapping leave: 2026-12-12 to 2026-12-18
        $resOverlap = $this->withHeaders($headers)->postJson('/api/v1/people/leave/requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-12-12',
            'end_date' => '2026-12-18',
            'reason' => 'Overlap test',
        ]);

        // Must reject with 422 Unprocessable Entity
        $resOverlap->assertStatus(422);
        $resOverlap->assertJson([
            'success' => false,
            'error' => [
                'code' => 'LEAVE_REQUEST_FAILED',
            ]
        ]);
    }

    /**
     * Test requesting more days than balance throws error.
     */
    public function test_insufficient_leave_quota_is_rejected(): void
    {
        $headers = ['X-Organization-Id' => $this->org->id];

        // Employee only has 14 days; requesting 20 days: 2026-05-01 to 2026-05-20
        $resExceed = $this->withHeaders($headers)->postJson('/api/v1/people/leave/requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-20',
            'reason' => 'Excessive leave request',
        ]);

        $resExceed->assertStatus(422);
        $resExceed->assertJson([
            'success' => false,
            'error' => [
                'code' => 'LEAVE_REQUEST_FAILED',
            ]
        ]);
    }
}
