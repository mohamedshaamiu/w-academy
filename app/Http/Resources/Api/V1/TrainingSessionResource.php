<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'squad' => $this->squad?->translated('name'),
            'scheduled_start' => $this->scheduled_start->toIso8601String(),
            'scheduled_end' => $this->scheduled_end->toIso8601String(),
            'venue' => $this->translated('venue'),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'coach' => $this->coach?->user?->name,
        ];
    }
}
