<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalRecord extends Model
{
    protected $fillable = [
        'pet_id',
        'appointment_id',
        'veterinarian_id',
        'consultation_date',
        'chief_complaint',
        'symptoms',
        'diagnosis',
        'weight',
        'temperature',
        'treatment',
        'observations',
        'next_control_date',
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
        'next_control_date' => 'date',
        'weight' => 'decimal:2',
        'temperature' => 'decimal:1',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    protected static function booted(): void
    {
        static::creating(function (ClinicalRecord $record) {

            if ($record->appointment_id) {

                $record->appointment?->update([
                    'status' => 'En atención',
                ]);
            }
        });
        static::saved(function (ClinicalRecord $record) {

            if ($record->weight !== null) {
                $record->pet?->update([
                    'weight' => $record->weight,
                ]);
            }

            if (
                $record->appointment &&
                filled($record->diagnosis)
            ) {
                $record->appointment->update([
                    'status' => 'Finalizada',
                ]);
            }
        });
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}