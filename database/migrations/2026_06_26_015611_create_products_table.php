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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            $table->string('name');
            $table->string('internal_code')->unique();
            $table->string('barcode')->nullable()->unique();

            $table->enum('type', [
                'Medicamento',
                'Vacuna',
                'Insumo',
                'Alimento',
                'Accesorio',
                'Material quirúrgico',
                'Laboratorio',
                'Otro',
            ]);

            $table->string('brand')->nullable();
            $table->string('presentation')->nullable();

            $table->text('description')->nullable();

            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);

            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
