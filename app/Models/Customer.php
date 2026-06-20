<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'dpi',
        'nit',
        'address',
        'notes',
        'is_active',
    ];

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
