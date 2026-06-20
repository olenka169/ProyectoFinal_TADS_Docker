<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_group_workdays', function (Blueprint $table) {

            $table->id();

            $table->foreignId('personnel_group_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('day');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_group_workdays');
    }
};