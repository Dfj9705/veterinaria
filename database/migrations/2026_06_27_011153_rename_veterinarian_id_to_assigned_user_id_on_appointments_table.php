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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['veterinarian_id']);

            $table->renameColumn('veterinarian_id', 'assigned_user_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('assigned_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_user_id_on_appointments', function (Blueprint $table) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['assigned_user_id']);

                $table->renameColumn('assigned_user_id', 'veterinarian_id');
            });

            Schema::table('appointments', function (Blueprint $table) {
                $table->foreign('veterinarian_id')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
            });
        });
    }
};
