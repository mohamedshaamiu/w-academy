<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'index_number' => $this->index_number,
            'full_name' => $this->full_name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'squad' => $this->whenLoaded('activeEnrolment', fn () => $this->activeEnrolment?->squad?->translated('name')),
            'has_signed_current_agreement' => $this->has_signed_current_agreement,
        ];
    }
}
