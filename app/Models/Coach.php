<?php

namespace App\Models;

use Database\Factories\CoachFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    /** @use HasFactory<CoachFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_no',
        'specialisation',
        'joined_on',
    ];

    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class, 'head_coach_id');
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }
}
