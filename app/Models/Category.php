<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bengali_name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class)->orderBy('name');
    }

    public function activeFoods(): HasMany
    {
        return $this->hasMany(Food::class)->where('is_active', true)->orderBy('name');
    }
}
