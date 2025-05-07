<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\RefJabatan;
use App\Models\RefBagian;
use App\Models\RefSubBagian;
use App\Models\RefShift;
use App\Models\RefLibur;
use App\Models\RefAgama;
use App\Models\RefCuti;
use App\Models\RefStatusPegawai;
use App\Models\RefJabatanStruktural;
use App\Models\RefJabatanFungsional;
use App\Models\RefEselon;
use App\Models\RefBerkas;
use App\Models\RefPayroll;
use App\Models\Employee;
use App\Observers\AuditTrailObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        // Mendaftarkan observer untuk audit trail
        RefJabatan::observe(AuditTrailObserver::class);
        RefBagian::observe(AuditTrailObserver::class);
        RefSubBagian::observe(AuditTrailObserver::class);
        RefShift::observe(AuditTrailObserver::class);
        RefLibur::observe(AuditTrailObserver::class);
        RefAgama::observe(AuditTrailObserver::class);
        RefCuti::observe(AuditTrailObserver::class);
        RefStatusPegawai::observe(AuditTrailObserver::class);
        RefJabatanStruktural::observe(AuditTrailObserver::class);
        RefJabatanFungsional::observe(AuditTrailObserver::class);
        RefEselon::observe(AuditTrailObserver::class);
        RefBerkas::observe(AuditTrailObserver::class);
        RefPayroll::observe(AuditTrailObserver::class);
        // Model Karyawan
        Employee::observe(AuditTrailObserver::class);
    }
}
