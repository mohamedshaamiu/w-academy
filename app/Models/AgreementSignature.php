<?php

namespace App\Models;

use App\Enums\AgreementStatus;
use App\Enums\Locale;
use App\Exceptions\SignatureIsImmutable;
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

    /**
     * The only columns a revocation may write (SPEC.md §8.3). `updated_at` is
     * permitted because Eloquent touches it on every save.
     *
     * @var array<int, string>
     */
    private const REVOCATION_COLUMNS = ['status', 'revoked_at', 'revoked_reason', 'updated_at'];

    /** Set only by AgreementService::revoke(); see SPEC.md §8.3. */
    private bool $revoking = false;

    /**
     * SPEC.md §8.3: a signature is immutable once created. Every column is
     * frozen; the three revocation columns open only for the duration of an
     * AgreementService::revoke() call.
     */
    protected static function booted(): void
    {
        static::updating(function (self $signature): void {
            $allowed = $signature->revoking ? self::REVOCATION_COLUMNS : ['updated_at'];

            $violations = array_values(array_diff(array_keys($signature->getDirty()), $allowed));

            if ($violations !== []) {
                throw SignatureIsImmutable::forColumns($violations);
            }
        });
    }

    /**
     * Opens the revocation columns for one save. Intended for
     * AgreementService::revoke() only — nothing else may call it.
     */
    public function markRevoking(): static
    {
        $this->revoking = true;

        return $this;
    }

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
