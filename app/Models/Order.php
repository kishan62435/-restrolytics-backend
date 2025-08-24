<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    //
    protected $fillable = ['restaurant_id', 'order_amount', 'order_time'];
    protected $casts = ['order_time' => 'datetime'];

    public function restaurant(): BelongsTo {
        return $this->belongsTo(Restaurant::class);
    }
}
