<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = Employee::with('costCenter')
            ->when($request->search, fn($q, $s) =>
                $q->where('display_name', 'like', "%{$s}%")
                  ->orWhere('employee_id', 'like', "%{$s}%")
                  ->orWhere('mail', 'like', "%{$s}%")
            )
            ->where('ad_status', 'active')
            ->orderBy('last_name')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters'   => $request->only(['search']),
        ]);
    }

    public function show(Employee $employee): Response
    {
        $employee->load('costCenter');

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }
}
