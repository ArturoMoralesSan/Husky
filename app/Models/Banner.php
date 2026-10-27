<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'alt_text',
        'link_url',
        'button_text',
        'open_in_new_tab',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
