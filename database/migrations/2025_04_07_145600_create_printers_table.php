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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vendor_id'); // Store as string for flexibility (e.g., '0x1234')
            $table->string('product_id'); // Store as string
            $table->string('serial_number')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Optional: Add unique constraint if needed, though logic handles it
            // $table->unique(['vendor_id', 'product_id', 'serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};