<x-layouts::app title="Expense Management">
    <div class="space-y-6" x-data="{ newExpenseModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Food Cart Expenses</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Track gas cylinders, raw ingredients, packaging, stall rent, electricity, and salaries</p>
            </div>

            <button
                type="button"
                @click="newExpenseModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Record New Expense</span>
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Today's Expenses</span>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">৳{{ number_format($todayExpenses, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">This Month's Expenses</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($thisMonthExpenses, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total All-Time Expenses</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($totalAllTime, 2) }}</p>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)]">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Recent Operational Expenses</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-start">Description</th>
                            <th class="py-3 px-4 text-start">Payment Method</th>
                            <th class="py-3 px-4 text-start">Reference</th>
                            <th class="py-3 px-4 text-end">Amount (৳)</th>
                            <th class="py-3 px-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $expense->date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-300">
                                        {{ $expense->category?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-[var(--fc-text)]">
                                    {{ $expense->description }}
                                </td>
                                <td class="py-3 px-4 uppercase font-bold text-[10px] text-[var(--fc-text-muted)]">
                                    {{ $expense->payment_method }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $expense->reference ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-end font-black text-sm text-red-500">
                                    ৳{{ number_format($expense->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Remove this expense entry?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-[var(--fc-text-muted)] hover:text-red-500">
                                            <flux:icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No expense records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $expenses->links() }}
            </div>
        </div>

        <!-- Add Expense Modal -->
        <div x-show="newExpenseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newExpenseModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Record New Operational Expense</h3>
                    <button type="button" @click="newExpenseModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Expense Category *</label>
                        <select name="expense_category_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Description *</label>
                        <input type="text" name="description" required placeholder="e.g. 12KG LP Gas Refill, 100 Foil Boxes" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Amount (৳) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="e.g. 1450" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold focus:border-[var(--fc-primary)] outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs">
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="card">Card / Bank</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Date *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Receipt / Invoice Ref #</label>
                        <input type="text" name="reference" placeholder="Optional voucher or bill number" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newExpenseModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
