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
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('work_from', ['office', 'flexi_time', 'pkl'])->default('office');
            $table->enum('shift', ['non_shift', 'night_shift', 'morning_shift', 'afternoon_shift', 'pkl', 'overtime', 'flexi_time'])->default('non_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('work_from');
            $table->dropColumn('shift');
        });
    }
};
