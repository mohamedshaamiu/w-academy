<?php

namespace App\Models;

use App\Enums\AgreementStatus;
use App\Enums\Locale;
use Database\Factories\AgreementSignatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AgreementSignature extends Model
{
    /** @use HasFactory<AgreementSignatureFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'agreement_template_id',
        'student_id',
        'guardian_id',
        'signed_at',
        'signatory_name',
        'signature_image_path',
        'consents',
        'signed_locale',
        'template_snapshot',
        'ip_address',
        'user_agent',
        'status',
        'revoked_at',
        'revoked_reason',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'consents' => 'array',
            'signed_locale' => Locale::class,
            'status' => AgreementStatus::class,
            'revoked_at' => 'datetime',
        ];
    }

    public function agreementTemplate(): BelongsTo
    {
        return $this->belongsTo(AgreementTemplate::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['student_id', 'guardian_id', 'agreement_template_id', 'status', 'signed_at'])
            ->logOnlyDirty();
    }
}
