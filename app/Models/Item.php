<?php
// app/Models/Item.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'image', 'price', 'sizes', 'has_sizes', 'status', 'created_by'];

    protected $casts = [
        'price' => 'array',
        'sizes' => 'array',
        'has_sizes' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get price for the current (or given) locale.
     * Returns scalar when no sizes, array when sizes exist.
     */
    public function priceFor(?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        $price = $this->price ?? [];

        return $price[$locale] ?? ($price[config('app.fallback_locale')] ?? null);
    }
}
