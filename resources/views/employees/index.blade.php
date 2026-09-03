<x-layouts::app title="Employee Management">
    <div class="space-y-6" x-data="{ newEmployeeModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Food Cart Staff & Employees</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Manage managers, cashiers, chefs, and helpers</p>
            </div>

            <button
                type="button"
                @click="newEmployeeModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Add Employee</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Active Team Members</span>
                    <p class="text-2xl font-black text-[var(--fc-text)] mt-1">{{ $totalEmployees }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
                    <flux:icon name="identification" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Monthly Payroll Commitment</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">৳{{ number_format($totalSalaryPayroll, 2) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
                    <flux:icon name="banknotes" class="size-6" />
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Name</th>
                            <th class="py-3 px-4 text-start">Phone</th>
                            <th class="py-3 px-4 text-start">Position</th>
                            <th class="py-3 px-4 text-end">Monthly Salary (৳)</th>
                            <th class="py-3 px-4 text-start">Joined Date</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $employee->name }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)] font-mono">
                                    {{ $employee->phone }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                                        {{ $employee->position }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end font-bold text-[var(--fc-text)]">
                                    ৳{{ number_format($employee->salary, 2) }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $employee->joining_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $employee->status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-zinc-500/10 text-zinc-500' }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No employees registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $employees->links() }}
            </div>
        </div>

        <!-- Add Employee Modal -->
        <div x-show="newEmployeeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newEmployeeModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Register New Employee</h3>
                    <button type="button" @click="newEmployeeModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('employees.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Employee Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. মোঃ শাকিল আহমেদ" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Phone Number (Bangladesh) *</label>
                        <input type="text" name="phone" required placeholder="01812-XXXXXX" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Role / Position *</label>
                            <select name="position" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs">
                                <option value="Manager">Manager</option>
                                <option value="Cashier">Cashier</option>
                                <option value="Chef">Chef / Cook</option>
                                <option value="Helper">Kitchen Helper</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Monthly Salary (৳) *</label>
                            <input type="number" step="100" name="salary" required placeholder="e.g. 15000" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Joining Date *</label>
                        <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newEmployeeModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Save Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
