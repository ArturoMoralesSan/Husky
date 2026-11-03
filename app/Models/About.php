<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class About extends Model
{
    use HasFactory;

    protected $fillable = [
        'who_we_are',
        'history',
        'mission',
        'vision',
        'objectives',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AboutValue::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function gallery(): MorphOne
    {
        return $this->morphOne(Gallery::class, 'model');
    }
}
