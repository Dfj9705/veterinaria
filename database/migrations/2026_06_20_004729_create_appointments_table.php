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
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('veterinarian_id')->constrained('users')->restrictOnDelete();

            $table->date('appointment_date');
            $table->time('appointment_time');

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

            $table->unique([
                'veterinarian_id',
                'appointment_date',
                'appointment_time',
            ], 'unique_veterinarian_schedule');
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
