<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrameworkStrikeLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'label' => $this->translated('label'),
            'type' => $this->translated('type'),
            'action' => $this->translated('action'),
            'parent_role' => $this->translated('parent_role'),
            'triggers_timeout' => $this->triggers_timeout,
            'triggers_suspension' => $this->triggers_suspension,
        ];
    }
}
