<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'prescription_number',
        'clinical_record_id',
        'pet_id',
        'customer_id',
        'veterinarian_id',
        'instructions',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(ClinicalRecord::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function clinicalRecord()
    {
        return $this->belongsTo(ClinicalRecord::class);
    }

    protected static function booted(): void
    {
        static::created(function ($prescription) {

            $prescription->updateQuietly([
                'prescription_number' =>
                    'REC-' . str_pad($prescription->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }
}