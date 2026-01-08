<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'dob',
        'address',
        'gender',
        'nationality',
        'is_suspended',
    ];

    protected $hidden = [
        // No password here - it's in credentials table
    ];

    protected $casts = [
        'id' => 'string',
        'role_id' => 'string',
        'dob' => 'date',
        'is_suspended' => 'boolean',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(Credential::class);
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role && $this->role->isAdmin();
    }

    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
