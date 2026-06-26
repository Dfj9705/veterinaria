<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($category) {
            if ($category->products()->exists()) {
                throw new \Exception(
                    'No se puede eliminar una categoría que tiene productos asociados.'
                );
            }
        });
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_inventory_categories') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_inventory_categories') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_inventory_categories') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_inventory_categories') ?? false;
    }
}
