<?php

namespace App\Models;

use App\Concerns\HasTranslatedAttributes;
use Database\Factories\FrameworkStrikeLevelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameworkStrikeLevel extends Model
{
    /** @use HasFactory<FrameworkStrikeLevelFactory> */
    use HasFactory, HasTranslatedAttributes;

    protected $fillable = [
        'level',
        'label_dv',
        'label_en',
        'type_dv',
        'type_en',
        'action_dv',
        'action_en',
        'parent_role_dv',
        'parent_role_en',
        'triggers_timeout',
        'timeout_minutes_min',
        'timeout_minutes_max',
        'triggers_parent_alert',
        'triggers_meeting',
        'triggers_suspension',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'triggers_timeout' => 'boolean',
            'triggers_parent_alert' => 'boolean',
            'triggers_meeting' => 'boolean',
            'triggers_suspension' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function isSuspensionTriggerLevel(): bool
    {
        return $this->level === (int) config('academy.suspension_trigger_level');
    }
}
