<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
