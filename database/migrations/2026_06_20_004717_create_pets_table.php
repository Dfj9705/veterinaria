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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('species_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('breed_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');

            $table->enum('sex', [
                'Macho',
                'Hembra',
            ])->nullable();

            $table->date('birth_date')->nullable();

            $table->decimal('weight', 8, 2)->nullable();

            $table->string('color')->nullable();

            $table->text('allergies')->nullable();

            $table->text('observations')->nullable();

            $table->enum('status', [
                'Activo',
                'Inactivo',
                'Fallecido',
            ])->default('Activo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
