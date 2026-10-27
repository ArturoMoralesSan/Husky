<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'length',
        'length_unit',
        'diameter',
        'diameter_unit',
        'width',
        'width_unit',
        'thickness',
        'thickness_unit',
        'specifications',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:3',
            'diameter' => 'decimal:3',
            'width' => 'decimal:3',
            'thickness' => 'decimal:3',
            'specifications' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
