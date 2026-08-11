<?php

namespace App\Models;

use App\Concerns\HasTranslatedAttributes;
use Database\Factories\AgreementTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgreementTemplate extends Model
{
    /** @use HasFactory<AgreementTemplateFactory> */
    use HasFactory, HasTranslatedAttributes;

    protected $fillable = [
        'version',
        'title_dv',
        'title_en',
        'body_dv',
        'body_en',
        'consent_clauses',
        'effective_from',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'consent_clauses' => 'array',
            'effective_from' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function agreementSignatures(): HasMany
    {
        return $this->hasMany(AgreementSignature::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
