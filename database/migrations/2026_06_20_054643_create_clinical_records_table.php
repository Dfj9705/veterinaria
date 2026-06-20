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
        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('veterinarian_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('consultation_date');

            $table->text('chief_complaint')->nullable();

            $table->text('symptoms')->nullable();

            $table->text('diagnosis')->nullable();

            $table->decimal('weight', 8, 2)->nullable();

            $table->decimal('temperature', 4, 1)->nullable();

            $table->text('treatment')->nullable();

            $table->text('observations')->nullable();

            $table->date('next_control_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_records');
    }
};
