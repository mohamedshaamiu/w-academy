<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /** Memoises the private-disk stat behind hasPhoto() for one instance. */
    private ?bool $photoExists = null;

    protected $fillable = [
        'index_number',
        'user_id',
        'full_name',
        'date_of_birth',
        'gender',
        'school_name',
        'class_level',
        'address',
        'photo_path',
        'status',
        'registered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'registered_at' => 'datetime',
            'status' => StudentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->using(GuardianStudent::class)
            ->withPivot(['relationship', 'is_primary', 'receives_alerts'])
            ->withTimestamps();
    }

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'squad_student')
            ->using(SquadStudent::class)
            ->withPivot(['enrolled_on', 'left_on', 'is_active'])
            ->withTimestamps();
    }

    public function activeEnrolment(): HasOne
    {
        return $this->hasOne(SquadStudent::class)->where('is_active', true);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function agreementSignatures(): HasMany
    {
        return $this->hasMany(AgreementSignature::class);
    }

    public function getAgeAttribute(): int
    {
        return (int) $this->date_of_birth->diffInYears(now());
    }

    public function getHasSignedCurrentAgreementAttribute(): bool
    {
        $current = AgreementTemplate::current()->first();

        if (! $current) {
            return false;
        }

        return $this->agreementSignatures()
            ->where('agreement_template_id', $current->id)
            ->where('status', 'signed')
            ->exists();
    }

    public function getPrimaryGuardianAttribute(): ?Guardian
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }

    /**
     * Whether a photo file is actually present on the private disk. Resolved
     * here rather than in a view so a missing file renders a placeholder
     * instead of a broken image (SPEC.md §8.6).
     */
    public function hasPhoto(): bool
    {
        return $this->photoExists ??= $this->photo_path !== null
            && Storage::disk('local')->exists($this->photo_path);
    }

    /**
     * The ONLY place a student photo URL is constructed (SPEC.md §8.6:
     * "signed URLs ... from a single shared construction site"). The route
     * additionally enforces `auth` and StudentPolicy@viewPhoto.
     */
    public function photoUrl(): ?string
    {
        return $this->hasPhoto()
            ? URL::signedRoute('students.photo', ['student' => $this->getKey()])
            : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['index_number', 'full_name', 'status', 'user_id'])
            ->logOnlyDirty();
    }
}
