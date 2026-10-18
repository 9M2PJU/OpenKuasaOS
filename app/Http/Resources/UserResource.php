<?php

namespace App\Http\Resources;

use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentOrg = OrganizationContext::get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'current_organization' => $currentOrg ? [
                'id' => $currentOrg->id,
                'name' => $currentOrg->name,
                'slug' => $currentOrg->slug,
            ] : null,
            'organizations' => $this->orgMembers->map(function ($member) {
                return [
                    'id' => $member->org_id,
                    'name' => $member->organization?->name,
                    'slug' => $member->organization?->slug,
                    'role' => $member->role,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
