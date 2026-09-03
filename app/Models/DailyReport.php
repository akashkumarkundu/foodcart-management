<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'total_orders',
        'total_customers',
        'total_sales',
        'cash_sales',
        'bkash_sales',
        'nagad_sales',
        'rocket_sales',
        'card_sales',
        'total_expenses',
        'total_waste',
        'net_profit',
        'profit_margin',
        'closed_by',
        'closed_at',
        'notes',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'total_sales' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'bkash_sales' => 'decimal:2',
            'nagad_sales' => 'decimal:2',
            'rocket_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'total_waste' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'profit_margin' => 'decimal:2',
            'closed_at' => 'datetime',
            'is_closed' => 'boolean',
        ];
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
