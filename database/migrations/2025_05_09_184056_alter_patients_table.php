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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['address', 'symptoms', 'bloodpressure', 'heartrate', 'temperature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->text('symptoms')->nullable();
            $table->string('bloodpressure')->nullable();
            $table->integer('heartrate')->nullable();
            $table->integer('temperature')->nullable();
        });
    }
};
