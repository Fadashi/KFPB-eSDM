<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\EmployeeImport;
use App\Exports\EmployeeExport;
use App\Exports\EmployeeImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class EmployeeImportExportController extends Controller
{
    public function export()
    {
        return Excel::download(new EmployeeExport, 'data_karyawan.xlsx');
    }

    public function template()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EmployeeImportTemplateExport, 'template_import_karyawan.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        try {
            $import = new \App\Imports\EmployeeImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            if (method_exists($import, 'failures') && $import->failures()->isNotEmpty()) {
                return back()->with([
                    'import_errors' => $import->failures(),
                    'message' => 'Beberapa data gagal diimport. Silakan cek error.',
                    'type' => 'error'
                ]);
            }

            return back()->with([
                'message' => 'Data karyawan berhasil diimport!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Terjadi kesalahan saat import: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
}
