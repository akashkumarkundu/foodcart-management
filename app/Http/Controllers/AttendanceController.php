<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $dateStr = $selectedDate->toDateString();

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        // Existing attendances for this date
        $attendances = Attendance::where('date', $dateStr)
            ->get()
            ->keyBy('employee_id');

        // Attendance stats for selected date
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $leaveCount = $attendances->where('status', 'leave')->count();

        return view('attendance.index', compact(
            'selectedDate',
            'employees',
            'attendances',
            'presentCount',
            'lateCount',
            'absentCount',
            'leaveCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['required', 'string', 'in:present,absent,late,leave'],
            'attendance.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $request->date;
        $user = $request->user();

        foreach ($request->attendance as $employeeId => $data) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date,
                ],
                [
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => $user?->id,
                ]
            );
        }

        return back()->with('success', "Attendance saved for {$date} successfully.");
    }
}
