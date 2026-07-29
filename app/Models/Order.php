<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_price',
        'status',
        'ordered_at'
    ];

    protected static function booted()
    {
        static::creating(function (self $order){
            $order->order_number = 'ORD-' . now()->format('YmdHis') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
