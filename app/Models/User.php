<?php

namespace App\Models;

use App\Enums\Locale;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'locale',
        'must_change_password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'locale' => Locale::class,
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function isGuardian(): bool
    {
        return $this->hasRole('guardian');
    }

    public function isCoach(): bool
    {
        return $this->hasRole('coach');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'phone', 'email', 'is_active', 'locale'])
            ->logOnlyDirty();
    }
}
