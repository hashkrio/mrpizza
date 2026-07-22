<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['name', 'email', 'mobile', 'address', 'logo', 'favicon', 'login_cover', 'created_by', 'currency_symbols'];

    public static function current()
    {
        return static::firstOrCreate(['id' => 1]);
    }

    protected $casts = [
        'currency_symbols' => 'array',
    ];
}
