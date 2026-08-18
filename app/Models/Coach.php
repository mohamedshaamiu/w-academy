<?php

namespace App\Models;

use Database\Factories\CoachFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class Coach extends Model
{
    /** @use HasFactory<CoachFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_no',
        'specialisation',
        'photo_path',
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

    public function hasPhoto(): bool
    {
        return $this->photo_path !== null;
    }

    /**
     * Coach photos live on the private disk and are served through the
     * signed `coaches.photo` route. Unlike student photos (SPEC.md §8.6)
     * they carry no policy check: coaches are academy staff and their
     * photos are shown on the public site, so the signature only stops
     * URL guessing, not viewing.
     */
    public function photoUrl(): ?string
    {
        return $this->hasPhoto()
            ? URL::signedRoute('coaches.photo', ['coach' => $this->getKey()])
            : null;
    }
}
