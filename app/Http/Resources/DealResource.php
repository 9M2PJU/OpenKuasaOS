<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'org_id' => $this->org_id,
            'contact_id' => $this->contact_id,
            'contact' => $this->whenLoaded('contact'),
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'stage_id' => $this->stage_id,
            'probability' => (int) $this->probability,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'order' => (int) $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
