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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            $table->string('number')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('pet_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('quotation_date');
            $table->date('valid_until')->nullable();

            $table->enum('status', [
                'Borrador',
                'Enviada',
                'Aceptada',
                'Rechazada',
                'Vencida',
                'Convertida',
            ])->default('Borrador');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
