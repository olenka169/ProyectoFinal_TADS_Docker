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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
            $table->string('name')->after('code')->nullable();
            $table->decimal('load_capacity', 10, 2)->after('year')->nullable()->comment('En Toneladas (Tn)');
            $table->decimal('fuel_capacity', 10, 2)->after('load_capacity')->nullable()->comment('En Litros (L)');
            $table->decimal('compaction_capacity', 10, 2)->after('fuel_capacity')->nullable()->comment('En Toneladas (Tn)');
            $table->integer('passenger_capacity')->after('compaction_capacity')->nullable();
            $table->text('description')->after('passenger_capacity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'name',
                'load_capacity',
                'fuel_capacity',
                'compaction_capacity',
                'passenger_capacity',
                'description'
            ]);
        });
    }
};
