<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'about_id',
        'name',
        'description',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function about(): BelongsTo
    {
        return $this->belongsTo(About::class);
    }
}
