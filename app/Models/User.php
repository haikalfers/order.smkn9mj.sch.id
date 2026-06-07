<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ─── Role Helpers ───────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDesain(): bool
    {
        return $this->role === 'desain';
    }

    public function isCetak(): bool
    {
        return $this->role === 'cetak';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'desain'      => 'Bagian Desain',
            'cetak'       => 'Bagian Cetak',
            default       => '-',
        };
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'admin_id');
    }

    public function designTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class, 'assigned_to');
    }

    public function productionTasks(): HasMany
    {
        return $this->hasMany(ProductionTask::class, 'assigned_to');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}
