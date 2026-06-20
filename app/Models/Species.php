<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    public function breeds()
    {
        return $this->hasMany(Breed::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
