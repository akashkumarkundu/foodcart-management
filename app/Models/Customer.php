<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'total_orders',
        'total_spent',
        'loyalty_points',
        'last_order_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_orders' => 'integer',
            'total_spent' => 'decimal:2',
            'loyalty_points' => 'integer',
            'last_order_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class)->latest();
    }

    /**
     * Average Order Value
     */
    public function getAverageOrderValueAttribute(): float
    {
        if ($this->total_orders <= 0) {
            return 0.0;
        }

        return round((float) $this->total_spent / $this->total_orders, 2);
    }
}
