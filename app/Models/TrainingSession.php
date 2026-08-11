<?php

namespace App\Models;

use App\Concerns\HasTranslatedAttributes;
use App\Enums\SessionStatus;
use Database\Factories\TrainingSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    /** @use HasFactory<TrainingSessionFactory> */
    use HasFactory, HasTranslatedAttributes;

    protected $table = 'training_sessions';

    protected $fillable = [
        'squad_id',
        'coach_id',
        'scheduled_start',
        'scheduled_end',
        'venue_dv',
        'venue_en',
        'status',
        'cancellation_reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'status' => SessionStatus::class,
        ];
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'training_session_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsReadOnlyForCoachAttribute(): bool
    {
        return $this->scheduled_start->lt(now()->subDays((int) config('academy.session_readonly_after_days')));
    }
}
