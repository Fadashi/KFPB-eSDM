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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('photo')->nullable();
            $table->string('nip')->unique();
            $table->string('entry_level')->nullable();
            $table->string('initial')->nullable();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->text('address');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->text('temporary_address')->nullable();
            $table->string('email')->unique();
            $table->string('home_phone')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('ktp')->nullable();
            $table->string('npwp')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('ref_bagian')->onDelete('set null');
            $table->foreignId('bank_id')->nullable()->constrained('banks')->onDelete('set null');
            $table->string('account_number')->nullable();
            $table->string('jamsostek')->nullable();
            $table->string('dplk')->nullable();
            $table->string('inhealth')->nullable();
            $table->foreignId('religion_id')->nullable()->constrained('ref_agama')->onDelete('set null');
            $table->foreignId('employee_type_id')->nullable()->constrained('ref_status_pegawai')->onDelete('set null');
            $table->string('grade')->nullable();
            $table->foreignId('functional_position_id')->nullable()->constrained('ref_jabatan_fungsional')->onDelete('set null');
            $table->foreignId('structural_position_id')->nullable()->constrained('ref_jabatan_struktural')->onDelete('set null');
            $table->foreignId('sub_department_id')->nullable()->constrained('ref_subbagian')->onDelete('set null');
            $table->foreignId('eselon_id')->nullable()->constrained('ref_eselon')->onDelete('set null');
            $table->enum('marital_status', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable();
            $table->enum('employee_status', ['Aktif', 'Non Aktif', 'Cuti'])->default('Aktif');
            $table->date('join_date');
            $table->date('contract_end_date')->nullable();
            $table->enum('education', ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'])->nullable();
            $table->date('position_date')->nullable();
            $table->string('position')->nullable();
            $table->enum('status', ['Aktif', 'Non Aktif', 'Cuti'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
}; 