<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->unique();

            $table->foreignId('personnel_type_id')
                ->constrained('personnel_types')
                ->onDelete('cascade');

            $table->string('names');
            $table->string('lastnames');
            $table->date('birthdate');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('status')->default('Activo');
            $table->string('password');
            $table->string('address');
            $table->string('photo_path')->nullable();
            $table->string('license_number', 9)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};