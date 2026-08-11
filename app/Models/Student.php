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
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['index_number', 'full_name', 'status', 'user_id'])
            ->logOnlyDirty();
    }
}
