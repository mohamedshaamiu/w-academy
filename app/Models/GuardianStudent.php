<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GuardianStudent extends Pivot
{
    use LogsActivity;

    public $incrementing = true;

    protected $table = 'guardian_student';

    protected $fillable = [
        'guardian_id',
        'student_id',
        'relationship',
        'is_primary',
        'receives_alerts',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'receives_alerts' => 'boolean',
        ];
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
