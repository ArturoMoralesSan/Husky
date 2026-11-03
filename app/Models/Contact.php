<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone_primary',
        'phone_secondary',
        'phone_tertiary',
        'address',
        'whatsapp',
        'schedule_weekdays',
        'schedule_saturday',
        'facebook_url',
        'instagram_url',
    ];
}