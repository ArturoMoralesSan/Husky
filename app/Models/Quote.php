<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pendiente',
        'en_revision',
        'enviada',
        'aceptada',
        'rechazada',
    ];

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'product_id',
        'service_id',
        'item_name',
        'description',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
