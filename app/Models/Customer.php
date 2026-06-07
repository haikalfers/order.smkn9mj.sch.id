<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
        'notes',
    ];

    // ─── Accessors ───────────────────────────────────────────────────

    /**
     * Ambil 2 huruf pertama nama untuk avatar inisial.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(mb_substr($word, 0, 1));
        }
        return $initials ?: '??';
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
