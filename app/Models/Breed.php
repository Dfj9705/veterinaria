<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    protected $fillable = [
        'species_id',
        'name',
        'is_active',
    ];

    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
