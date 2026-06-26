<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'expiration_date',
        'quantity',
        'cost_price',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

}
