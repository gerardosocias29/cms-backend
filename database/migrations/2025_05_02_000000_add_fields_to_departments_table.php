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
        Schema::table('departments', function (Blueprint $table) {
            $table->integer('staffCount')->default(0)->after('description');
            $table->integer('totalBeds')->default(0)->after('staffCount');
            $table->enum('status', ['available', 'busy', 'full'])->default('available')->after('totalBeds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['staffCount', 'totalBeds', 'status']);
        });
    }
};
