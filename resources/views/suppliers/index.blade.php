<x-layouts::app title="Suppliers Management">
    <div class="space-y-6" x-data="{ newSupplierModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Suppliers Directory</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Manage raw material vendors, wholesale spice & poultry suppliers, and credit dues</p>
            </div>

            <button
                type="button"
                @click="newSupplierModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Add Supplier</span>
            </button>
        </div>

        <!-- Supplier KPI Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Purchase Volume</span>
                    <p class="text-2xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($totalPurchased, 2) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
                    <flux:icon name="truck" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between border-red-500/30 bg-red-500/5">
                <div>
                    <span class="text-xs font-bold text-red-700 dark:text-red-300">Total Outstanding Dues to Suppliers</span>
                    <p class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">৳{{ number_format($totalDues, 2) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-red-500/20 text-red-600">
                    <flux:icon name="banknotes" class="size-6" />
                </div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Supplier Name</th>
                            <th class="py-3 px-4 text-start">Contact Person</th>
                            <th class="py-3 px-4 text-start">Phone</th>
                            <th class="py-3 px-4 text-start">Supplied Goods</th>
                            <th class="py-3 px-4 text-end">Purchased (৳)</th>
                            <th class="py-3 px-4 text-end">Due Balance (৳)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $supplier->name }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $supplier->contact_person ?? '-' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-[var(--fc-text)]">
                                    {{ $supplier->phone }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)] max-w-xs truncate">
                                    {{ $supplier->products_supplied ?? 'Raw materials' }}
                                </td>
                                <td class="py-3 px-4 text-end font-bold">
                                    ৳{{ number_format($supplier->total_purchased, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-black {{ $supplier->balance_due > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                    ৳{{ number_format($supplier->balance_due, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $supplier->status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-zinc-500/10 text-zinc-500' }}">
                                        {{ $supplier->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $suppliers->links() }}
            </div>
        </div>

        <!-- Add Supplier Modal -->
        <div x-show="newSupplierModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newSupplierModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Add New Supplier</h3>
                    <button type="button" @click="newSupplierModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Company / Business Name *</label>
                        <input type="text" name="name" required placeholder="e.g. কারওয়ান বাজার পোল্ট্রি" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Contact Person</label>
                        <input type="text" name="contact_person" placeholder="e.g. মোঃ রফিকুল ইসলাম" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Phone *</label>
                            <input type="text" name="phone" required placeholder="01712-XXXXXX" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Email</label>
                            <input type="email" name="email" placeholder="Optional" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Supplied Products</label>
                        <input type="text" name="products_supplied" placeholder="e.g. দেশি মুরগি, ব্রয়লার, গরুর মাংস" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Address</label>
                        <textarea name="address" rows="2" placeholder="e.g. কারওয়ান বাজার, ঢাকা" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"></textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newSupplierModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Save Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
