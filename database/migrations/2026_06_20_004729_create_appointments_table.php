<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('veterinarian_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('appointment_date');

            $table->time('appointment_time');

            $table->unsignedInteger('duration_minutes')
                ->comment('Duración de la cita en minutos, tomada del servicio');

            $table->enum('status', [
                'Programada',
                'Confirmada',
                'En atención',
                'Finalizada',
                'Cancelada',
                'No asistió',
            ])->default('Programada');

            $table->text('reason')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'veterinarian_id',
                'appointment_date',
                'appointment_time',
            ], 'appointments_veterinarian_schedule_index');

            $table->index([
                'customer_id',
                'pet_id',
            ], 'appointments_customer_pet_index');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
