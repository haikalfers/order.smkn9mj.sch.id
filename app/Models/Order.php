<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'admin_id',
        'status',
        'deadline',
        'total_price',
        'notes',
        'payment_status',
        'dp_amount',
    ];

    protected $casts = [
        'deadline'    => 'date',
        'total_price' => 'decimal:2',
        'dp_amount'   => 'decimal:2',
    ];

    // ─── Status Constants ─────────────────────────────────────────────

    const STATUS_LABELS = [
        'pending'        => 'Pending',
        'design_process' => 'Proses Desain',
        'design_done'    => 'Desain Selesai',
        'production'     => 'Produksi',
        'done'           => 'Selesai',
        'delivered'      => 'Terkirim',
        'cancelled'      => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'pending'        => 'gray',
        'design_process' => 'blue',
        'design_done'    => 'indigo',
        'production'     => 'yellow',
        'done'           => 'green',
        'delivered'      => 'teal',
        'cancelled'      => 'red',
    ];

    // ─── Booted ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $year  = now()->format('Y');
                $count = static::whereYear('created_at', $year)->withTrashed()->count() + 1;
                $order->order_number = 'ORD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function isOverdue(): bool
    {
        return $this->deadline
            && $this->deadline->isPast()
            && !in_array($this->status, ['done', 'delivered', 'cancelled']);
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function designTask(): HasOne
    {
        return $this->hasOne(DesignTask::class);
    }

    public function productionTask(): HasOne
    {
        return $this->hasOne(ProductionTask::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('payment_date');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->total_price - $this->total_paid);
    }
}
