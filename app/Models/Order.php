<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addons()
    {
        return $this->hasMany(OrderAddon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($order) {
            $order->updateQuietly([
                'order_no' => 'ORD' . date('Y') . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }
}