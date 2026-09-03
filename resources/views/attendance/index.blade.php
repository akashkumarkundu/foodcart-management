<x-layouts::app title="Attendance Tracker">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Daily Staff Attendance</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Track shifts, late arrivals, and leaves for cart staff</p>
            </div>

            <!-- Date Selector -->
            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate->toDateString() }}"
                    onchange="this.form.submit()"
                    class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none font-bold"
                />
            </form>
        </div>

        <!-- Attendance Stats for Selected Date -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Present Today</span>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $presentCount }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Late Arrival</span>
                <p class="text-2xl font-black text-amber-500 mt-1">{{ $lateCount }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Absent</span>
                <p class="text-2xl font-black text-red-500 mt-1">{{ $absentCount }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Approved Leave</span>
                <p class="text-2xl font-black text-blue-500 mt-1">{{ $leaveCount }}</p>
            </div>
        </div>

        <!-- Mark Attendance Sheet -->
        <div class="fc-card p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">
                    Attendance Roster for {{ $selectedDate->format('l, d F Y') }}
                </h2>
            </div>

            <form method="POST" action="{{ route('attendance.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}" />

                <div class="divide-y divide-[var(--fc-border)]">
                    @foreach($employees as $employee)
                        @php
                            $currentStatus = $attendances[$employee->id]->status ?? 'present';
                            $currentNotes = $attendances[$employee->id]->notes ?? '';
                        @endphp
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-xs text-[var(--fc-text)]">{{ $employee->name }}</h4>
                                <span class="text-[11px] text-[var(--fc-text-muted)]">{{ $employee->position }} • {{ $employee->phone }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Status Radio Selector -->
                                <div class="flex items-center gap-2 text-xs">
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="attendance[{{ $employee->id }}][status]" value="present" {{ $currentStatus === 'present' ? 'checked' : '' }} class="text-emerald-500" />
                                        <span class="text-emerald-600 font-semibold">Present</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="attendance[{{ $employee->id }}][status]" value="late" {{ $currentStatus === 'late' ? 'checked' : '' }} class="text-amber-500" />
                                        <span class="text-amber-600 font-semibold">Late</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="attendance[{{ $employee->id }}][status]" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }} class="text-red-500" />
                                        <span class="text-red-600 font-semibold">Absent</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="attendance[{{ $employee->id }}][status]" value="leave" {{ $currentStatus === 'leave' ? 'checked' : '' }} class="text-blue-500" />
                                        <span class="text-blue-600 font-semibold">Leave</span>
                                    </label>
                                </div>

                                <input
                                    type="text"
                                    name="attendance[{{ $employee->id }}][notes]"
                                    value="{{ $currentNotes }}"
                                    placeholder="Notes (optional)"
                                    class="w-32 sm:w-44 px-2 py-1 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)]"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-[var(--fc-border)] flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-extrabold text-xs shadow-md">
                        Save Attendance Records
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
