<x-layouts::app title="Customer Management">
    <div class="space-y-6" x-data="{ newCustomerModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Customer Directory</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Customer spending profiles, order frequencies, and loyalty balances</p>
            </div>

            <button
                type="button"
                @click="newCustomerModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Register Customer</span>
            </button>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Registered Customers</span>
                    <p class="text-2xl font-black text-[var(--fc-text)] mt-1">{{ $totalCustomers }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
                    <flux:icon name="users" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Active Loyalty Points</span>
                    <p class="text-2xl font-black text-amber-500 mt-1">{{ number_format($totalLoyaltyPoints) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-500">
                    <flux:icon name="star" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Customer Spending</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">৳{{ number_format($totalCustomerSpent, 2) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
                    <flux:icon name="currency-bangladeshi" class="size-6" />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Customer Directory</h2>
                <form method="GET" action="{{ route('customers.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search name or phone..."
                        class="px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] outline-none"
                    />
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs">
                        Search
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Name</th>
                            <th class="py-3 px-4 text-start">Phone</th>
                            <th class="py-3 px-4 text-center">Orders</th>
                            <th class="py-3 px-4 text-end">Total Spent (৳)</th>
                            <th class="py-3 px-4 text-end">Avg Order (৳)</th>
                            <th class="py-3 px-4 text-center">Loyalty Points</th>
                            <th class="py-3 px-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    <a href="{{ route('customers.show', $customer) }}" class="hover:text-[var(--fc-primary)]">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-semibold text-[var(--fc-text-muted)]">
                                    {{ $customer->phone }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold">{{ $customer->total_orders }}</td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">
                                    ৳{{ number_format($customer->total_spent, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end text-[var(--fc-text-muted)]">
                                    ৳{{ number_format($customer->average_order_value, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-500/10 text-amber-600">
                                        <flux:icon name="star" class="size-3 text-amber-500" />
                                        {{ $customer->loyalty_points }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <a href="{{ route('customers.show', $customer) }}" class="p-1.5 rounded border border-[var(--fc-border)] hover:bg-[var(--fc-bg)] inline-block text-[var(--fc-primary)]">
                                        <flux:icon name="eye" class="size-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No customers registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $customers->links() }}
            </div>
        </div>

        <!-- Add Customer Modal -->
        <div x-show="newCustomerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newCustomerModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Register Customer Profile</h3>
                    <button type="button" @click="newCustomerModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('customers.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Customer Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. তানভীর আহমেদ" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Mobile Phone Number (Bangladesh) *</label>
                        <input type="text" name="phone" required placeholder="01712-XXXXXX" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="Optional" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Delivery / Residential Address</label>
                        <textarea name="address" rows="2" placeholder="Road, House, Area (e.g. Dhanmondi, Dhaka)" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none"></textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newCustomerModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
