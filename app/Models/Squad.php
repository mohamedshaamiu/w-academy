<?php

namespace App\Models;

use App\Concerns\HasTranslatedAttributes;
use Database\Factories\SquadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Squad extends Model
{
    /** @use HasFactory<SquadFactory> */
    use HasFactory, HasTranslatedAttributes;

    protected $fillable = [
        'name_dv',
        'name_en',
        'age_group',
        'head_coach_id',
        'venue_dv',
        'venue_en',
        'training_days',
        'default_start_time',
        'default_end_time',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'training_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function headCoach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'head_coach_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'squad_student')
            ->using(SquadStudent::class)
            ->withPivot(['enrolled_on', 'left_on', 'is_active'])
            ->withTimestamps();
    }

    public function activeStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_active', true);
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function getActiveStudentCountAttribute(): int
    {
        return $this->activeStudents()->count();
    }
}
