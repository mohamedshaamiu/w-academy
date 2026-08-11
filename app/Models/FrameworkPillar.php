<?php

namespace App\Models;

use App\Concerns\HasTranslatedAttributes;
use Database\Factories\FrameworkPillarFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameworkPillar extends Model
{
    /** @use HasFactory<FrameworkPillarFactory> */
    use HasFactory, HasTranslatedAttributes;

    protected $fillable = [
        'code',
        'name_dv',
        'name_en',
        'description_dv',
        'description_en',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
