<x-layouts::app title="Settings & Themes">
    <div class="max-w-4xl mx-auto space-y-8" x-data="{ currentTheme: localStorage.getItem('foodcart360_theme') || 'modern-light' }">
        <!-- Header -->
        <div class="pb-2 border-b border-[var(--fc-border)]">
            <h1 class="text-2xl font-black text-[var(--fc-text)]">Food Cart Settings & Appearance</h1>
            <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Customize your food cart business identity, receipt headers, and visual themes</p>
        </div>

        <!-- 6 Themes Visual Selector -->
        <div class="fc-card p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-base font-bold text-[var(--fc-text)]">6 Visual Appearance Themes</h2>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Click any theme card to preview and apply across all POS, management, and analytics interfaces</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Theme 1: Modern Light -->
                <div
                    @click="window.FoodCart360.setTheme('modern-light'); currentTheme = 'modern-light';"
                    :class="currentTheme === 'modern-light' ? 'ring-2 ring-emerald-500 border-emerald-500 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-[var(--fc-border)] bg-white text-zinc-900 cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Modern Light</span>
                        <span x-show="currentTheme === 'modern-light'" class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-emerald-500 rounded"></div>
                        <div class="flex-1 bg-slate-100 rounded border border-slate-200"></div>
                        <div class="flex-1 bg-white rounded border border-slate-200"></div>
                    </div>
                    <p class="text-[11px] text-zinc-500">Clean, crisp emerald accents on soft zinc slate.</p>
                </div>

                <!-- Theme 2: Dark Mode -->
                <div
                    @click="window.FoodCart360.setTheme('dark-mode'); currentTheme = 'dark-mode';"
                    :class="currentTheme === 'dark-mode' ? 'ring-2 ring-emerald-500 border-emerald-500 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-zinc-700 bg-zinc-900 text-white cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Dark Mode</span>
                        <span x-show="currentTheme === 'dark-mode'" class="text-[10px] font-black text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-emerald-500 rounded"></div>
                        <div class="flex-1 bg-zinc-800 rounded"></div>
                        <div class="flex-1 bg-zinc-950 rounded"></div>
                    </div>
                    <p class="text-[11px] text-zinc-400">Night-shift friendly high contrast dark interface.</p>
                </div>

                <!-- Theme 3: Warm Food -->
                <div
                    @click="window.FoodCart360.setTheme('warm-food'); currentTheme = 'warm-food';"
                    :class="currentTheme === 'warm-food' ? 'ring-2 ring-amber-500 border-amber-500 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-amber-200 bg-[#fffbeb] text-amber-950 cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Warm Food</span>
                        <span x-show="currentTheme === 'warm-food'" class="text-[10px] font-black text-amber-800 bg-amber-200 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-amber-500 rounded"></div>
                        <div class="flex-1 bg-orange-100 rounded"></div>
                        <div class="flex-1 bg-amber-50 rounded"></div>
                    </div>
                    <p class="text-[11px] text-amber-700">Appetizing warm amber and terracotta restaurant tones.</p>
                </div>

                <!-- Theme 4: Fresh Restaurant -->
                <div
                    @click="window.FoodCart360.setTheme('fresh-restaurant'); currentTheme = 'fresh-restaurant';"
                    :class="currentTheme === 'fresh-restaurant' ? 'ring-2 ring-teal-500 border-teal-500 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-teal-200 bg-[#f0fdfa] text-teal-950 cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Fresh Restaurant</span>
                        <span x-show="currentTheme === 'fresh-restaurant'" class="text-[10px] font-black text-teal-800 bg-teal-200 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-teal-600 rounded"></div>
                        <div class="flex-1 bg-teal-100 rounded"></div>
                        <div class="flex-1 bg-white rounded"></div>
                    </div>
                    <p class="text-[11px] text-teal-700">Crisp mint & teal herbal vibes for organic street cafes.</p>
                </div>

                <!-- Theme 5: Premium Black -->
                <div
                    @click="window.FoodCart360.setTheme('premium-black'); currentTheme = 'premium-black';"
                    :class="currentTheme === 'premium-black' ? 'ring-2 ring-amber-400 border-amber-400 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-zinc-800 bg-black text-zinc-100 cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Premium Black</span>
                        <span x-show="currentTheme === 'premium-black'" class="text-[10px] font-black text-amber-400 bg-zinc-900 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-amber-400 rounded"></div>
                        <div class="flex-1 bg-zinc-900 rounded"></div>
                        <div class="flex-1 bg-black rounded"></div>
                    </div>
                    <p class="text-[11px] text-zinc-400">OLED pure black with luxury metallic gold accents.</p>
                </div>

                <!-- Theme 6: Bangladesh Inspired -->
                <div
                    @click="window.FoodCart360.setTheme('bangladesh'); currentTheme = 'bangladesh';"
                    :class="currentTheme === 'bangladesh' ? 'ring-2 ring-red-500 border-red-500 shadow-md' : 'hover:border-zinc-400'"
                    class="p-4 rounded-xl border border-emerald-300 bg-[#ecfdf5] text-emerald-950 cursor-pointer transition-all space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs">Bangladesh Inspired</span>
                        <span x-show="currentTheme === 'bangladesh'" class="text-[10px] font-black text-red-700 bg-red-100 px-2 py-0.5 rounded">ACTIVE</span>
                    </div>
                    <div class="flex gap-1.5 h-6">
                        <div class="flex-1 bg-[#006a4e] rounded"></div>
                        <div class="flex-1 bg-[#f42a41] rounded"></div>
                        <div class="flex-1 bg-white rounded"></div>
                    </div>
                    <p class="text-[11px] text-emerald-800">Rich bottle green with patriotic crimson red highlights.</p>
                </div>
            </div>
        </div>

        <!-- Food Cart Configuration Form -->
        <div class="fc-card p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-base font-bold text-[var(--fc-text)]">Food Cart Profile & Invoice Details</h2>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">These details print at the top of your 80mm thermal receipts and customer invoices</p>
            </div>

            <form method="POST" action="{{ route('settings.cart.update') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Food Cart Business Name *</label>
                        <input
                            type="text"
                            name="cart_name"
                            value="{{ old('cart_name', $cartName) }}"
                            required
                            placeholder="e.g. ঢাকাইয়া ফুড কার্ট / Dhakaiya Food Cart"
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Cart Contact Phone *</label>
                        <input
                            type="text"
                            name="cart_phone"
                            value="{{ old('cart_phone', $cartPhone) }}"
                            required
                            placeholder="01712-345678"
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Cart Physical Stall Location / Address *</label>
                    <input
                        type="text"
                        name="cart_address"
                        value="{{ old('cart_address', $cartAddress) }}"
                        required
                        placeholder="e.g. Stall #14, Food Street, Dhanmondi 27, Dhaka"
                        class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Base Currency</label>
                        <input
                            type="text"
                            value="BDT (৳) - Bangladeshi Taka"
                            disabled
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs font-bold text-[var(--fc-text-muted)] cursor-not-allowed"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">System Timezone</label>
                        <input
                            type="text"
                            value="Asia/Dhaka (UTC+6)"
                            disabled
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs font-bold text-[var(--fc-text-muted)] cursor-not-allowed"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Tax / VAT (%)</label>
                        <input
                            type="number"
                            step="0.1"
                            name="tax_percentage"
                            value="{{ old('tax_percentage', $taxPercentage) }}"
                            placeholder="0"
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Receipt Footer Bengali Message</label>
                    <input
                        type="text"
                        name="receipt_footer"
                        value="{{ old('receipt_footer', $receiptFooter) }}"
                        placeholder="ধন্যবাদ! আবার আসবেন।"
                        class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <div class="pt-3 border-t border-[var(--fc-border)] flex justify-end">
                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black text-xs shadow-md hover:opacity-95"
                    >
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
