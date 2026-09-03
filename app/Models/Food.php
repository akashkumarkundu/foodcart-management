<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Food extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'foods';

    protected static function booted(): void
    {
        static::creating(function (Food $food) {
            if (empty($food->slug)) {
                $slug = Str::slug($food->name);
                $originalSlug = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }
                $food->slug = $slug;
            }
        });
    }

    protected $fillable = [
        'category_id',
        'name',
        'bengali_name',
        'slug',
        'image',
        'description',
        'selling_price',
        'cost_price',
        'preparation_time',
        'current_stock',
        'min_stock',
        'unit',
        'is_active',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'preparation_time' => 'integer',
            'current_stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wastes(): HasMany
    {
        return $this->hasMany(Waste::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->reviews()->avg('rating') ?: 5.0), 1);
    }

    /**
     * Profit per single item (Selling Price - Cost Price)
     */
    public function getProfitPerItemAttribute(): float
    {
        return max(0, (float) ($this->selling_price - $this->cost_price));
    }

    /**
     * Profit margin percentage
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->selling_price <= 0) {
            return 0.0;
        }

        return round(($this->profit_per_item / $this->selling_price) * 100, 2);
    }

    /**
     * Check if stock is low
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    /**
     * Scope for low stock
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock');
    }

    /**
     * Scope for active food
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the authentic image URL for the food item
     */
    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image) && (str_starts_with($this->image, 'http') || file_exists(public_path($this->image)))) {
            return str_starts_with($this->image, 'http') ? $this->image : asset($this->image);
        }

        $slug = strtolower($this->slug ?: Str::slug($this->name));
        $categorySlug = strtolower($this->category?->slug ?? '');

        // Item-specific authentic image mapping
        $mapping = [
            'burger' => '/images/foods/burger.jpg',
            'shawarma' => '/images/foods/shawarma.jpg',
            'wrap' => '/images/foods/shawarma.jpg',
            'roll' => '/images/foods/shawarma.jpg',
            'fries' => '/images/foods/fries.jpg',
            'wings' => '/images/foods/kebab.jpg',
            'cha' => '/images/foods/tea.jpg',
            'tea' => '/images/foods/tea.jpg',
            'coffee' => '/images/foods/coffee.jpg',
            'chowmein' => '/images/foods/chowmein.jpg',
            'noodles' => '/images/foods/chowmein.jpg',
            'pasta' => '/images/foods/pasta.jpg',
            'tehari' => '/images/foods/tehari.jpg',
            'halim' => '/images/foods/halim.jpg',
            'chotpoti' => '/images/foods/chotpoti.jpg',
            'doi-fuchka' => '/images/foods/doi_fuchka.jpg',
            'fuchka' => '/images/foods/fuchka.jpg',
            'fuska' => '/images/foods/fuchka.jpg',
            'kebab' => '/images/foods/kebab.jpg',
        ];

        foreach ($mapping as $key => $path) {
            if (str_contains($slug, $key)) {
                return asset($path);
            }
        }

        // Category-based fallback
        $categoryMapping = [
            'fast-food' => '/images/foods/burger.jpg',
            'tea-coffee' => '/images/foods/tea.jpg',
            'noodles' => '/images/foods/chowmein.jpg',
            'pasta' => '/images/foods/pasta.jpg',
            'chicken-tehari' => '/images/foods/tehari.jpg',
            'halim' => '/images/foods/halim.jpg',
            'chotpoti' => '/images/foods/chotpoti.jpg',
            'fuska' => '/images/foods/fuchka.jpg',
            'kebab-items' => '/images/foods/kebab.jpg',
        ];

        if (isset($categoryMapping[$categorySlug])) {
            return asset($categoryMapping[$categorySlug]);
        }

        return asset('/images/foods/burger.jpg');
    }
}
