<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'times_used',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'starts_at' => 'date',
            'expires_at' => 'date',
            'usage_limit' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if coupon is currently valid for a given subtotal
     */
    public function isValidForAmount(float $subtotal, ?string &$errorMessage = null): bool
    {
        if (! $this->is_active) {
            $errorMessage = 'This coupon is no longer active.';

            return false;
        }

        $today = Carbon::today();

        if ($this->starts_at && $today->lt($this->starts_at)) {
            $errorMessage = 'This coupon has not started yet.';

            return false;
        }

        if ($this->expires_at && $today->gt($this->expires_at)) {
            $errorMessage = 'This coupon has expired.';

            return false;
        }

        if ($this->usage_limit && $this->times_used >= $this->usage_limit) {
            $errorMessage = 'This coupon has reached its usage limit.';

            return false;
        }

        if ($subtotal < (float) $this->min_order_amount) {
            $errorMessage = 'Minimum order amount for this coupon is ৳'.number_format($this->min_order_amount, 2);

            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isValidForAmount($subtotal)) {
            return 0.0;
        }

        $discount = 0.0;

        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * (float) $this->discount_value) / 100;
        } else {
            $discount = (float) $this->discount_value;
        }

        if ($this->max_discount_amount && $discount > (float) $this->max_discount_amount) {
            $discount = (float) $this->max_discount_amount;
        }

        return min($subtotal, round($discount, 2));
    }
}
