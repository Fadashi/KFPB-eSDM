<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PositionModal from '@/Components/PositionModal.vue';
import DepartmentModal from '@/Components/DepartmentModal.vue';
import SubDepartmentModal from '@/Components/SubDepartmentModal.vue';
import ShiftModal from '@/Components/ShiftModal.vue';
import ReligionModal from '@/Components/ReligionModal.vue';
import StructureModal from '@/Components/StructureModal.vue';
import HolidayModal from '@/Components/HolidayModal.vue';
import LeaveModal from '@/Components/LeaveModal.vue';
import EmployeeStatusModal from '@/Components/EmployeeStatusModal.vue';
import BankModal from '@/Components/BankModal.vue';
import GradingModal from '@/Components/GradingModal.vue';
import StructuralPositionModal from '@/Components/StructuralPositionModal.vue';
import FunctionalPositionModal from '@/Components/FunctionalPositionModal.vue';
import EselonModal from '@/Components/EselonModal.vue';
import DocumentModal from '@/Components/DocumentModal.vue';
import PayrollModal from '@/Components/PayrollModal.vue';

const sidebarCollapsed = ref(false);
const handleSidebarCollapse = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed;
};

const showPositionModal = ref(false);
const showDepartmentModal = ref(false);
const showSubDepartmentModal = ref(false);
const showShiftModal = ref(false);
const showReligionModal = ref(false);
const showStructureModal = ref(false);
const showHolidayModal = ref(false);
const showLeaveModal = ref(false);
const showEmployeeStatusModal = ref(false);
const showBankModal = ref(false);
const showGradingModal = ref(false);
const showStructuralPositionModal = ref(false);
const showFunctionalPositionModal = ref(false);
const showEselonModal = ref(false);
const showDocumentModal = ref(false);
const showPayrollModal = ref(false);
</script>

<template>
  <Head title="Referensi" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Referensi
      </h2>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
      <!-- Jabatan -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showPositionModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-briefcase mr-2 text-blue-600"></i> Data Jabatan
        </h2>
        <p class="text-gray-600 text-sm mt-2">Kelola jabatan dan tanggung jawab karyawan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>
      
      <!-- Bagian -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showDepartmentModal = true">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-sitemap mr-2 text-blue-600"></i> Data Bagian
        </h2>
        <p>Informasi tentang struktur organisasi.</p>
        <div class="text-blue-500 hover:underline font-bold">Lihat Detail</div>
      </div>

      <!-- Sub Bagian -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showSubDepartmentModal = true">
        <h2 class="font-semibold text-lg flex items-center">
          <i class="fas fa-sitemap mr-2 text-blue-600"></i> Data Sub Bagian
        </h2>
        <p>Informasi tentang struktur organisasi.</p>
        <div class="text-blue-500 hover:underline font-bold">Lihat Detail</div>
      </div>

      <!-- Shift -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showShiftModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-clock mr-2 text-blue-600"></i> Data Shift
        </h2>
        <p class="text-gray-600 text-sm mt-2">Detail jam kerja dan pembagian shift.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Agama -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showReligionModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-praying-hands mr-2 text-blue-600"></i> Data Agama
        </h2>
        <p class="text-gray-600 text-sm mt-2">Data agama karyawan untuk administrasi.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Struktur -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showStructureModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-users-cog mr-2 text-blue-600"></i> Data Struktur
        </h2>
        <p class="text-gray-600 text-sm mt-2">Hierarki dan hubungan antar jabatan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Libur -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showHolidayModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-user-clock mr-2 text-blue-600"></i> Data Libur
        </h2>
        <p class="text-gray-600 text-sm mt-2">Hari libur dan cuti bersama.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Cuti -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showLeaveModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-calendar-alt mr-2 text-blue-600"></i> Data Cuti
        </h2>
        <p class="text-gray-600 text-sm mt-2">Jenis cuti dan prosedur pengajuan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Status Pegawai -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showEmployeeStatusModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-user-tag mr-2 text-blue-600"></i> Status Pegawai
        </h2>
        <p class="text-gray-600 text-sm mt-2">Status kepegawaian karyawan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Bank -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showBankModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-university mr-2 text-blue-600"></i> Data Bank
        </h2>
        <p class="text-gray-600 text-sm mt-2">Data bank untuk penggajian.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Grading -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showGradingModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-chart-line mr-2 text-blue-600"></i> Data Grading
        </h2>
        <p class="text-gray-600 text-sm mt-2">Sistem grading untuk penilaian karyawan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Jabatan Struktural -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showStructuralPositionModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-user-tie mr-2 text-blue-600"></i> Jabatan Struktural
        </h2>
        <p class="text-gray-600 text-sm mt-2">Jabatan struktural dan tanggung jawab.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Jabatan Fungsional -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showFunctionalPositionModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-user-friends mr-2 text-blue-600"></i> Jabatan Fungsional
        </h2>
        <p class="text-gray-600 text-sm mt-2">Peran jabatan fungsional di perusahaan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Eselon -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showEselonModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-file-alt mr-2 text-blue-600"></i> Data Eselon
        </h2>
        <p class="text-gray-600 text-sm mt-2">Kriteria jabatan eselon dalam organisasi.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Berkas -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showDocumentModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-file-invoice mr-2 text-blue-600"></i> Data Berkas
        </h2>
        <p class="text-gray-600 text-sm mt-2">Berkas penting yang harus dikelola.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>

      <!-- Payroll -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 transition-all hover:shadow-md cursor-pointer" @click="showPayrollModal = true">
        <h2 class="font-semibold text-lg flex items-center text-gray-700">
          <i class="fas fa-money-check-alt mr-2 text-blue-600"></i> Data Payroll
        </h2>
        <p class="text-gray-600 text-sm mt-2">Informasi penggajian dan tunjangan karyawan.</p>
        <div class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-3">Lihat Detail</div>
      </div>
    </div>

    <!-- Modal Jabatan -->
    <PositionModal
      v-if="showPositionModal"
      :show="showPositionModal"
      @close="showPositionModal = false"
    />

    <!-- Modal Bagian -->
    <DepartmentModal
      v-if="showDepartmentModal"
      :show="showDepartmentModal"
      @close="showDepartmentModal = false"
    />

    <!-- Modal Sub Bagian -->
    <SubDepartmentModal
      v-if="showSubDepartmentModal"
      :show="showSubDepartmentModal"
      @close="showSubDepartmentModal = false"
    />

    <!-- Modal Shift -->
    <ShiftModal
      v-if="showShiftModal"
      :show="showShiftModal"
      @close="showShiftModal = false"
    />

    <!-- Modal Agama -->
    <ReligionModal
      v-if="showReligionModal"
      :show="showReligionModal"
      @close="showReligionModal = false"
    />

    <!-- Modal Struktur -->
    <StructureModal
      v-if="showStructureModal"
      :show="showStructureModal"
      @close="showStructureModal = false"
    />

    <!-- Modal Libur -->
    <HolidayModal
      v-if="showHolidayModal"
      :show="showHolidayModal"
      @close="showHolidayModal = false"
    />

    <!-- Modal Cuti -->
    <LeaveModal
      v-if="showLeaveModal"
      :show="showLeaveModal"
      @close="showLeaveModal = false"
    />

    <!-- Modal Status Pegawai -->
    <EmployeeStatusModal
      v-if="showEmployeeStatusModal"
      :show="showEmployeeStatusModal"
      @close="showEmployeeStatusModal = false"
    />

    <!-- Modal Bank -->
    <BankModal
      v-if="showBankModal"
      :show="showBankModal"
      @close="showBankModal = false"
    />

    <!-- Modal Grading -->
    <GradingModal
      v-if="showGradingModal"
      :show="showGradingModal"
      @close="showGradingModal = false"
    />

    <!-- Modal Jabatan Struktural -->
    <StructuralPositionModal
      v-if="showStructuralPositionModal"
      :show="showStructuralPositionModal"
      @close="showStructuralPositionModal = false"
    />

    <!-- Modal Jabatan Fungsional -->
    <FunctionalPositionModal
      v-if="showFunctionalPositionModal"
      :show="showFunctionalPositionModal"
      @close="showFunctionalPositionModal = false"
    />

    <!-- Modal Eselon -->
    <EselonModal
      v-if="showEselonModal"
      :show="showEselonModal"
      @close="showEselonModal = false"
    />

    <!-- Modal Berkas -->
    <DocumentModal
      v-if="showDocumentModal"
      :show="showDocumentModal"
      @close="showDocumentModal = false"
    />

    <!-- Modal Payroll -->
    <PayrollModal
      v-if="showPayrollModal"
      :show="showPayrollModal"
      @close="showPayrollModal = false"
    />
  </AuthenticatedLayout>
</template>

<style scoped></style>
