<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    public const CATEGORY_PROJECTS = 'proyectos';

    public const CATEGORY_MAQUILA = 'maquila';

    public const CATEGORIES = [
        self::CATEGORY_PROJECTS,
        self::CATEGORY_MAQUILA,
    ];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'short_description',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function gallery(): MorphOne
    {
        return $this->morphOne(Gallery::class, 'model');
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (blank($service->slug)) {
                $service->slug = Str::slug($service->name);
            }

            if (blank($service->category)) {
                $service->category = self::CATEGORY_PROJECTS;
            }
        });
    }
}
