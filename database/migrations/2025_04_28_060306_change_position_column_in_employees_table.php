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
        Schema::table('employees', function (Blueprint $table) {
            // Hapus kolom position
            $table->dropColumn('position');
            
            // Tambah kolom position_id sebagai foreign key
            $table->foreignId('position_id')->nullable()->after('position_date')->constrained('ref_jabatan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Hapus kolom position_id
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            
            // Tambah kembali kolom position
            $table->string('position')->nullable()->after('position_date');
        });
    }
};
