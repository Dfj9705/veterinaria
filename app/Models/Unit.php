<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($unit) {
            if ($unit->products()->exists()) {
                throw new \Exception(
                    'No se puede eliminar una unidad utilizada por productos.'
                );
            }
        });
    }
}
