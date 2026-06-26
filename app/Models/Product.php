<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'inventory_category_id',
        'unit_id',
        'name',
        'internal_code',
        'barcode',
        'type',
        'brand',
        'presentation',
        'description',
        'cost_price',
        'sale_price',
        'current_stock',
        'minimum_stock',
        'is_active',
        'uses_batches',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'is_active' => 'boolean',
        'uses_batches' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->internal_code)) {
                $product->internal_code = self::generateInternalCode();
            }
        });

        static::updating(function (Product $product) {
            if (blank($product->internal_code)) {
                $product->internal_code = self::generateInternalCode();
            }
        });


    }

    public static function generateInternalCode(): string
    {
        $lastProduct = self::query()
            ->whereNotNull('internal_code')
            ->latest('id')
            ->first();

        $nextNumber = $lastProduct
            ? ((int) str_replace('INV-', '', $lastProduct->internal_code)) + 1
            : 1;

        return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
