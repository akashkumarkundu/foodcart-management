<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waste extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id',
        'user_id',
        'quantity',
        'unit',
        'estimated_cost',
        'reason',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Readable reason label
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'burned' => 'Burned',
            'expired' => 'Expired',
            'overproduction' => 'Overproduction',
            'damaged' => 'Damaged',
            'spoiled' => 'Spoiled / Sour',
            'customer_return' => 'Customer Return',
            default => 'Other',
        };
    }
}
