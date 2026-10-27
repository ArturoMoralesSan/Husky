<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'material',
        'treatment',
        'retention',
        'retention_unit',
        'image',
        'image_alt',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'retention' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}
