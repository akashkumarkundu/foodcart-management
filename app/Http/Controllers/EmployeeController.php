<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::withCount('attendances');

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalEmployees = Employee::count();
        $totalSalaryPayroll = (float) Employee::where('status', 'active')->sum('salary');

        return view('employees.index', compact('employees', 'totalEmployees', 'totalSalaryPayroll'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'in:Manager,Cashier,Chef,Helper'],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['required', 'date'],
        ]);

        $validated['status'] = 'active';

        Employee::create($validated);

        return back()->with('success', 'Employee profile created successfully.');
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'in:Manager,Cashier,Chef,Helper'],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $employee->update($validated);

        return back()->with('success', 'Employee details updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return back()->with('success', 'Employee record removed.');
    }
}
