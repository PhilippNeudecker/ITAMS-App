<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LdapAuthService;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = Employee::with('costCenter')
            ->when($request->search, fn ($q, $s) => $q->where('display_name', 'like', "%{$s}%")
                ->orWhere('employee_id', 'like', "%{$s}%")
                ->orWhere('mail', 'like', "%{$s}%")
            )
            ->where('ad_status', 'active')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'costcenters' => CostCenter::orderBy('cost_center_code')->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Employee $employee): Response
    {
        $employee->load('costCenter');

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function syncLDAP(LdapAuthService $ldap): RedirectResponse
    {
        try {
            $stats = $ldap->syncAllEmployees();
        } catch (\Throwable $e) {
            Log::error('LDAP-Sync fehlgeschlagen: '.$e->getMessage());

            return redirect()->route('employees.index')
                ->with('error', 'LDAP-Synchronisierung fehlgeschlagen: '.$e->getMessage());
        }

        $message = sprintf(
            '%d neu, %d aktualisiert, %d deaktiviert.',
            $stats['created'],
            $stats['updated'],
            $stats['deactivated'],
        );

        if ($stats['skipped'] > 0) {
            $message .= " {$stats['skipped']} übersprungen (keine Personalnummer).";
        }

        if (! empty($stats['errors'])) {
            $message .= ' '.count($stats['errors']).' Fehler.';
            Log::warning('LDAP-Sync Fehler', $stats['errors']);
        }

        return redirect()->route('employees.index')->with('success', $message);
    }
}
