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
            $table->integer('starting_department_id')->nullable();
            $table->integer('next_department_id')->nullable();
            $table->timestamp('next_department_started')->nullable();
            $table->json('prev_department_ids')->nullable();
            $table->timestamp('session_started')->nullable();
            $table->timestamp('session_ended')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
