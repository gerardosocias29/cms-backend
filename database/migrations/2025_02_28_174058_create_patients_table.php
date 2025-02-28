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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('priority_number');
            $table->string('name');
            $table->timestamp('birthday')->nullable();
            $table->string('priority')->nullable();
            $table->text('address')->nullable();
            $table->text('symptoms')->nullable();
            $table->string('bloodpressure')->nullable();
            $table->integer('heartrate')->nullable();
            $table->integer('temperature')->nullable();
            $table->enum('status', ['waiting','in-progress','completed'])->nullable();
            $table->integer('assigned_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
