<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Inertia\Inertia;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of audit trails.
     */
    public function index()
    {
        $audits = AuditTrail::with('user')->latest()->get();

        return Inertia::render('Admin/Audit-trail', [
            'audits' => $audits,
        ]);
    }
}
