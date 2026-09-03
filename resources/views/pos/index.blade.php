<x-layouts::app title="Point of Sale (POS)">
    <div x-data="posTerminal()" x-init="init()" class="space-y-4">
        <!-- POS Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[var(--fc-border)]">
            <div class="flex items-center gap-3">
                <div class="size-9 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] flex items-center justify-center shadow-xs">
                    <flux:icon name="shopping-bag" class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-[var(--fc-text)]">Food Cart POS Terminal</h1>
                    <p class="text-xs text-[var(--fc-text-muted)]">Fast touch checkout • Bangladeshi payments</p>
                </div>
            </div>

            <!-- Search and Mobile Cart Toggle Button -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1 sm:w-64">
                    <flux:icon name="magnifying-glass" class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--fc-text-muted)]" />
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Search food (e.g. Burger, খিচুড়ি)..."
                        class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] focus:ring-1 focus:ring-[var(--fc-primary)] outline-none"
                    />
                </div>

                <!-- Mobile Cart Toggle Button -->
                <button
                    type="button"
                    @click="deviceView === 'mobile' ? activeMobileTab = 'cart' : mobileCartOpen = true"
                    :class="deviceView === 'mobile' ? 'flex' : 'lg:hidden flex'"
                    class="relative items-center gap-2 px-3 py-1.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] text-xs font-bold shadow-md"
                >
                    <flux:icon name="shopping-cart" class="size-4" />
                    <span x-text="'৳' + totalAmount.toFixed(2)">৳0.00</span>
                    <span x-show="cart.length > 0" class="size-4 rounded-full bg-white text-[var(--fc-primary)] font-black text-[10px] flex items-center justify-center" x-text="cart.reduce((s, i) => s + i.qty, 0)"></span>
                </button>
            </div>
        </div>

        <!-- Mobile Tab Switcher (Menu vs Cart) -->
        <div x-show="deviceView === 'mobile'" class="grid grid-cols-2 p-1 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] text-xs font-bold shadow-xs">
            <button
                type="button"
                @click="activeMobileTab = 'menu'"
                :class="activeMobileTab === 'menu' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] shadow-xs' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]'"
                class="py-2 rounded-xl transition-all flex items-center justify-center gap-1.5"
            >
                <span>🍽️ খাবার মেনু</span>
            </button>
            <button
                type="button"
                @click="activeMobileTab = 'cart'"
                :class="activeMobileTab === 'cart' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] shadow-xs' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]'"
                class="py-2 rounded-xl transition-all flex items-center justify-center gap-1.5 relative"
            >
                <span>🛒 অর্ডার কার্ট</span>
                <span x-show="cart.length > 0" class="px-1.5 py-0.5 rounded-full bg-white text-[var(--fc-primary)] font-black text-[10px]" x-text="cart.reduce((s, i) => s + i.qty, 0)"></span>
            </button>
        </div>

        <!-- POS Workspace: Left Food Catalog (7 cols) + Right Active Cart (5 cols) -->
        <div :class="deviceView === 'mobile' ? 'space-y-4' : 'grid grid-cols-1 lg:grid-cols-12 gap-6 items-start'">
            <!-- Left: Categories & Foods Grid -->
            <div :class="deviceView === 'mobile' ? (activeMobileTab === 'menu' ? 'block space-y-3' : 'hidden') : 'lg:col-span-7 space-y-4'">
                <!-- Category Filter Pills (Horizontal scrollable) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <button
                        type="button"
                        @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold shadow-xs' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                        class="px-3 py-1.5 rounded-full text-xs shrink-0 transition-all font-medium"
                    >
                        All Items
                    </button>

                    @foreach($categories as $category)
                        <button
                            type="button"
                            @click="selectedCategory = {{ $category->id }}"
                            :class="selectedCategory === {{ $category->id }} ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold shadow-xs' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                            class="px-3 py-1.5 rounded-full text-xs shrink-0 transition-all font-medium flex items-center gap-1.5"
                        >
                            <span>{{ $category->name }}</span>
                            @if($category->bengali_name)
                                <span class="opacity-70 text-[10px]">({{ $category->bengali_name }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <!-- Food Cards Grid -->
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2.5' : 'grid grid-cols-2 sm:grid-cols-3 gap-3'">
                    <template x-for="food in filteredFoods" :key="food.id">
                        <div
                            @click="addToCart(food)"
                            class="fc-card p-3 rounded-2xl cursor-pointer hover:border-[var(--fc-primary)] hover:shadow-md transition-all group relative flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <h3 class="font-bold text-xs sm:text-sm text-[var(--fc-text)] group-hover:text-[var(--fc-primary)] transition-colors line-clamp-1" x-text="food.bengali_name || food.name"></h3>
                                    <span
                                        class="text-[9px] font-bold px-1.5 py-0.5 rounded shrink-0"
                                        :class="food.current_stock <= 5 ? 'bg-red-500/10 text-red-600' : 'bg-emerald-500/10 text-emerald-600'"
                                        x-text="food.current_stock + ' left'"
                                    ></span>
                                </div>
                                <p class="text-[10px] text-[var(--fc-text-muted)] line-clamp-1" x-text="food.name"></p>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-[var(--fc-border)]/60 flex items-center justify-between">
                                <span class="font-black text-sm text-emerald-600 dark:text-emerald-400" x-text="'৳' + parseFloat(food.selling_price).toFixed(2)"></span>
                                <span class="size-7 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] flex items-center justify-center group-hover:scale-105 transition-transform shadow-xs">
                                    <flux:icon name="plus" class="size-4" />
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filteredFoods.length === 0" class="text-center py-12 fc-card p-6">
                    <flux:icon name="magnifying-glass" class="size-10 text-[var(--fc-text-muted)] mx-auto mb-2 opacity-50" />
                    <p class="text-xs text-[var(--fc-text-muted)]">No food items match your filter.</p>
                </div>

                <!-- Floating Mobile Cart Bar -->
                <div
                    x-show="deviceView === 'mobile' && activeMobileTab === 'menu' && cart.length > 0"
                    x-transition
                    class="sticky bottom-2 z-30 p-2.5 rounded-2xl bg-slate-900/95 backdrop-blur border border-emerald-500/40 shadow-xl flex items-center justify-between"
                >
                    <div class="flex items-center gap-2 pl-1.5">
                        <div class="size-8 rounded-xl bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shadow-xs">
                            <span x-text="cart.reduce((s, i) => s + i.qty, 0)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">মোট আইটেম বিল</span>
                            <span class="text-sm font-black text-emerald-400" x-text="'৳' + totalAmount.toFixed(2)"></span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="activeMobileTab = 'cart'"
                        class="py-2 px-4 rounded-xl daraz-gradient text-white font-black text-xs shadow-sm hover:brightness-110 flex items-center gap-1.5"
                    >
                        <span>কার্ট ও বিল</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            <!-- Right: Interactive Live Cart & Checkout (Desktop view & Mobile Tab) -->
            <div :class="deviceView === 'mobile' ? (activeMobileTab === 'cart' ? 'block space-y-4' : 'hidden') : 'hidden lg:block lg:col-span-5 space-y-4'">
                <div class="fc-card p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                        <div class="flex items-center gap-2">
                            <flux:icon name="shopping-cart" class="size-5 text-[var(--fc-primary)]" />
                            <h2 class="font-bold text-sm text-[var(--fc-text)]">Current Order Items</h2>
                        </div>
                        <button
                            type="button"
                            @click="clearCart()"
                            x-show="cart.length > 0"
                            class="text-[11px] text-red-500 hover:underline font-semibold"
                        >
                            Clear All
                        </button>
                    </div>

                    <!-- Customer Selector -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-[var(--fc-text-muted)]">Customer (Optional)</label>
                            <button type="button" @click="showCustomerModal = true" class="text-[11px] font-bold text-[var(--fc-primary)] hover:underline">
                                + Quick Add
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input
                                type="text"
                                x-model="customerName"
                                placeholder="Customer Name"
                                class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                            />
                            <input
                                type="text"
                                x-model="customerPhone"
                                placeholder="Phone (01XXXXXXXXX)"
                                class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                            />
                        </div>
                    </div>

                    <!-- Cart Items Table / List -->
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="p-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs text-[var(--fc-text)] truncate" x-text="item.name"></h4>
                                    <p class="text-[10px] text-[var(--fc-text-muted)]" x-text="'৳' + item.price.toFixed(2) + ' each'"></p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        type="button"
                                        @click="decreaseQty(index)"
                                        class="size-6 rounded-md border border-[var(--fc-border)] bg-[var(--fc-card)] flex items-center justify-center font-bold text-xs hover:border-[var(--fc-primary)]"
                                    >-</button>
                                    <span class="w-6 text-center font-black text-xs" x-text="item.qty"></span>
                                    <button
                                        type="button"
                                        @click="increaseQty(index)"
                                        class="size-6 rounded-md border border-[var(--fc-border)] bg-[var(--fc-card)] flex items-center justify-center font-bold text-xs hover:border-[var(--fc-primary)]"
                                    >+</button>
                                </div>

                                <div class="text-end shrink-0 w-16">
                                    <span class="font-black text-xs text-[var(--fc-text)]" x-text="'৳' + (item.price * item.qty).toFixed(2)"></span>
                                </div>

                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="text-[var(--fc-text-muted)] hover:text-red-500 p-1"
                                >
                                    <flux:icon name="x-mark" class="size-3.5" />
                                </button>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="text-center py-8 border-2 border-dashed border-[var(--fc-border)] rounded-xl">
                            <flux:icon name="shopping-bag" class="size-8 text-[var(--fc-text-muted)] mx-auto mb-1.5 opacity-40" />
                            <p class="text-xs font-semibold text-[var(--fc-text-muted)]">Cart is currently empty</p>
                            <p class="text-[10px] text-[var(--fc-text-muted)] mt-0.5">Click items on the left to add</p>
                        </div>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="pt-2 border-t border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                x-model="couponCode"
                                placeholder="Coupon Code (e.g. FOOD50)"
                                class="flex-1 px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs uppercase font-semibold text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                            />
                            <button
                                type="button"
                                @click="applyCoupon()"
                                class="px-3 py-1.5 rounded-lg border border-[var(--fc-primary)] bg-[var(--fc-primary)]/10 text-[var(--fc-primary)] font-bold text-xs hover:bg-[var(--fc-primary)] hover:text-white transition-colors"
                            >
                                Apply
                            </button>
                        </div>
                        <p x-show="couponMessage" class="text-[11px] font-semibold mt-1" :class="couponValid ? 'text-emerald-600' : 'text-red-500'" x-text="couponMessage"></p>
                    </div>

                    <!-- Bill Totals -->
                    <div class="pt-3 border-t border-[var(--fc-border)] space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-[var(--fc-text-muted)]">
                            <span>Subtotal</span>
                            <span class="font-semibold" x-text="'৳' + subtotal.toFixed(2)"></span>
                        </div>
                        <div x-show="discountAmount > 0" class="flex items-center justify-between text-emerald-600 font-semibold">
                            <span>Coupon Discount</span>
                            <span x-text="'- ৳' + discountAmount.toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between text-base font-black text-[var(--fc-text)] pt-2 border-t border-[var(--fc-border)]">
                            <span>Grand Total</span>
                            <span class="text-emerald-600 dark:text-emerald-400 text-lg" x-text="'৳' + totalAmount.toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Checkout Trigger Button -->
                    <button
                        type="button"
                        :disabled="cart.length === 0"
                        @click="openPaymentModal()"
                        class="w-full py-3 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-extrabold text-sm shadow-md hover:opacity-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2"
                    >
                        <flux:icon name="banknotes" class="size-5" />
                        <span>Proceed to Payment (৳<span x-text="totalAmount.toFixed(2)"></span>)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Modal (Cash with change, bKash, Nagad, Rocket, Card) -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="showPaymentModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Complete Order Payment</h3>
                    <button type="button" @click="showPaymentModal = false" class="text-[var(--fc-text-muted)] hover:text-red-500">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <div class="text-center py-2 bg-[var(--fc-bg)] rounded-xl border border-[var(--fc-border)]">
                    <p class="text-xs text-[var(--fc-text-muted)] font-medium">Total Amount Payable</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400" x-text="'৳' + totalAmount.toFixed(2)"></p>
                </div>

                <!-- Payment Method Selectors -->
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-[var(--fc-text-muted)]">Select Payment Channel</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-500/10 font-bold text-emerald-700 dark:text-emerald-300' : 'border-[var(--fc-border)] hover:bg-[var(--fc-bg)]'"
                            class="p-2 rounded-xl border text-xs text-center flex flex-col items-center gap-1 transition-all"
                        >
                            <flux:icon name="banknotes" class="size-5 text-emerald-500" />
                            <span>Cash</span>
                        </button>

                        <button
                            type="button"
                            @click="paymentMethod = 'bkash'"
                            :class="paymentMethod === 'bkash' ? 'border-pink-500 bg-pink-500/10 font-bold text-pink-700 dark:text-pink-300' : 'border-[var(--fc-border)] hover:bg-[var(--fc-bg)]'"
                            class="p-2 rounded-xl border text-xs text-center flex flex-col items-center gap-1 transition-all"
                        >
                            <span class="size-5 font-black text-pink-600 flex items-center justify-center">bK</span>
                            <span>bKash</span>
                        </button>

                        <button
                            type="button"
                            @click="paymentMethod = 'nagad'"
                            :class="paymentMethod === 'nagad' ? 'border-orange-500 bg-orange-500/10 font-bold text-orange-700 dark:text-orange-300' : 'border-[var(--fc-border)] hover:bg-[var(--fc-bg)]'"
                            class="p-2 rounded-xl border text-xs text-center flex flex-col items-center gap-1 transition-all"
                        >
                            <span class="size-5 font-black text-orange-600 flex items-center justify-center">ন</span>
                            <span>Nagad</span>
                        </button>

                        <button
                            type="button"
                            @click="paymentMethod = 'rocket'"
                            :class="paymentMethod === 'rocket' ? 'border-purple-500 bg-purple-500/10 font-bold text-purple-700 dark:text-purple-300' : 'border-[var(--fc-border)] hover:bg-[var(--fc-bg)]'"
                            class="p-2 rounded-xl border text-xs text-center flex flex-col items-center gap-1 transition-all"
                        >
                            <span class="size-5 font-black text-purple-600 flex items-center justify-center">R</span>
                            <span>Rocket</span>
                        </button>

                        <button
                            type="button"
                            @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'border-blue-500 bg-blue-500/10 font-bold text-blue-700 dark:text-blue-300' : 'border-[var(--fc-border)] hover:bg-[var(--fc-bg)]'"
                            class="p-2 rounded-xl border text-xs text-center flex flex-col items-center gap-1 transition-all"
                        >
                            <flux:icon name="credit-card" class="size-5 text-blue-500" />
                            <span>Card</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Tender & Change Calculator -->
                <div x-show="paymentMethod === 'cash'" class="space-y-2 p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                    <label class="text-xs font-semibold text-[var(--fc-text-muted)]">Cash Received from Customer</label>
                    <input
                        type="number"
                        x-model="cashTendered"
                        placeholder="e.g. 500 or 1000"
                        class="w-full px-3 py-2 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-sm font-bold text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                    <div class="flex items-center justify-between text-xs pt-1 font-semibold">
                        <span class="text-[var(--fc-text-muted)]">Change to Return:</span>
                        <span
                            class="text-sm font-black"
                            :class="cashChange >= 0 ? 'text-emerald-600' : 'text-red-500'"
                            x-text="'৳' + (cashChange >= 0 ? cashChange.toFixed(2) : '0.00 (Short)')"
                        ></span>
                    </div>
                </div>

                <!-- Mobile Banking Transaction ID / Reference (bKash, Nagad, Rocket) -->
                <div x-show="paymentMethod === 'bkash' || paymentMethod === 'nagad' || paymentMethod === 'rocket'" class="space-y-2 p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                    <label class="text-xs font-semibold text-[var(--fc-text-muted)]" x-text="paymentMethod.toUpperCase() + ' Transaction ID / Ref'"></label>
                    <input
                        type="text"
                        x-model="transactionId"
                        placeholder="e.g. BK789X123 or Reference #"
                        class="w-full px-3 py-2 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold uppercase text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <!-- Card Approval Code -->
                <div x-show="paymentMethod === 'card'" class="space-y-2 p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                    <label class="text-xs font-semibold text-[var(--fc-text-muted)]">Card Slip / Approval Code</label>
                    <input
                        type="text"
                        x-model="transactionId"
                        placeholder="e.g. POS-987654"
                        class="w-full px-3 py-2 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <div class="pt-2">
                    <button
                        type="button"
                        :disabled="isProcessing"
                        @click="submitOrder()"
                        class="w-full py-3 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-extrabold text-sm shadow-md hover:opacity-95 transition-all flex items-center justify-center gap-2"
                    >
                        <flux:icon name="check-circle" class="size-5" />
                        <span x-text="isProcessing ? 'Processing Order...' : 'Confirm & Print Bill (৳' + totalAmount.toFixed(2) + ')'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success & Printable Receipt Modal -->
        <div x-show="completedOrder" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs"></div>
            <div class="relative w-full max-w-sm fc-card p-6 shadow-2xl z-10 text-center space-y-4">
                <div class="size-12 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center mx-auto">
                    <flux:icon name="check" class="size-7" />
                </div>

                <div>
                    <h3 class="font-black text-lg text-[var(--fc-text)]">Order Placed Successfully!</h3>
                    <p class="text-xs text-[var(--fc-text-muted)] mt-0.5" x-text="'Order #' + (completedOrder?.order_number || '')"></p>
                </div>

                <div class="p-4 rounded-xl bg-[var(--fc-bg)] text-xs text-start space-y-1 font-mono border border-[var(--fc-border)]">
                    <div class="flex justify-between">
                        <span>Total Paid:</span>
                        <span class="font-bold" x-text="'৳' + (completedOrder?.total_amount || 0)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Method:</span>
                        <span class="font-bold uppercase" x-text="completedOrder?.payment_method || ''"></span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a
                        :href="invoiceUrl"
                        target="_blank"
                        class="flex-1 py-2.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm"
                    >
                        <flux:icon name="printer" class="size-4" />
                        <span>Print Bill</span>
                    </a>

                    <button
                        type="button"
                        @click="newOrder()"
                        class="flex-1 py-2.5 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] hover:bg-[var(--fc-bg)]"
                    >
                        Next Order
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Bottom Cart Slide-up Sheet -->
        <div x-show="mobileCartOpen" x-cloak :class="deviceView === 'mobile' ? 'fixed inset-0 z-50 flex flex-col justify-end' : 'fixed inset-0 z-50 lg:hidden flex flex-col justify-end'">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="mobileCartOpen = false"></div>
            <div class="relative w-full max-h-[85vh] bg-[var(--fc-card)] rounded-t-2xl p-5 shadow-2xl z-10 flex flex-col space-y-4 overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <div class="flex items-center gap-2">
                        <flux:icon name="shopping-cart" class="size-5 text-[var(--fc-primary)]" />
                        <h3 class="font-bold text-sm text-[var(--fc-text)]">Mobile Order Cart</h3>
                    </div>
                    <button type="button" @click="mobileCartOpen = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <!-- Customer Fields -->
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="customerName" placeholder="Customer Name" class="px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs" />
                    <input type="text" x-model="customerPhone" placeholder="Phone" class="px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs" />
                </div>

                <!-- Mobile Items List -->
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="p-2.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-bg)] flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-[var(--fc-text)] truncate" x-text="item.name"></p>
                                <p class="text-[10px] text-[var(--fc-text-muted)]" x-text="'৳' + item.price.toFixed(2)"></p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="decreaseQty(index)" class="size-6 rounded border border-[var(--fc-border)] font-bold text-xs">-</button>
                                <span class="w-6 text-center font-bold text-xs" x-text="item.qty"></span>
                                <button type="button" @click="increaseQty(index)" class="size-6 rounded border border-[var(--fc-border)] font-bold text-xs">+</button>
                            </div>
                            <span class="font-black text-xs text-[var(--fc-text)] w-14 text-end" x-text="'৳' + (item.price * item.qty).toFixed(2)"></span>
                        </div>
                    </template>
                </div>

                <!-- Bill Totals -->
                <div class="pt-2 border-t border-[var(--fc-border)] space-y-1 text-xs">
                    <div class="flex justify-between text-base font-black text-[var(--fc-text)]">
                        <span>Grand Total:</span>
                        <span class="text-emerald-600" x-text="'৳' + totalAmount.toFixed(2)"></span>
                    </div>
                </div>

                <button
                    type="button"
                    :disabled="cart.length === 0"
                    @click="mobileCartOpen = false; openPaymentModal();"
                    class="w-full py-3 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black text-sm shadow-md"
                >
                    Proceed to Payment (৳<span x-text="totalAmount.toFixed(2)"></span>)
                </button>
            </div>
        </div>
    </div>

    <!-- Alpine POS Logic Component -->
    <script>
        function posTerminal() {
            return {
                activeMobileTab: 'menu',
                allFoods: @json($allFoods),
                categories: @json($categories),
                selectedCategory: 'all',
                searchQuery: '',
                cart: [],
                customerName: '',
                customerPhone: '',
                couponCode: '',
                discountAmount: 0.0,
                couponValid: false,
                couponMessage: '',
                showPaymentModal: false,
                mobileCartOpen: false,
                paymentMethod: 'cash',
                cashTendered: '',
                transactionId: '',
                isProcessing: false,
                completedOrder: null,
                invoiceUrl: '',

                init() {
                    // Preload cart from session or blank
                },

                get filteredFoods() {
                    return this.allFoods.filter(food => {
                        const matchesCategory = this.selectedCategory === 'all' || food.category_id == this.selectedCategory;
                        const query = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !query ||
                            food.name.toLowerCase().includes(query) ||
                            (food.bengali_name && food.bengali_name.toLowerCase().includes(query));
                        return matchesCategory && matchesSearch;
                    });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get totalAmount() {
                    return Math.max(0, this.subtotal - this.discountAmount);
                },

                get cashChange() {
                    const tender = parseFloat(this.cashTendered) || 0;
                    return tender - this.totalAmount;
                },

                addToCart(food) {
                    const existing = this.cart.find(i => i.id === food.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            id: food.id,
                            name: food.name,
                            price: parseFloat(food.selling_price),
                            qty: 1
                        });
                    }
                    this.recalcCoupon();
                },

                increaseQty(index) {
                    this.cart[index].qty++;
                    this.recalcCoupon();
                },

                decreaseQty(index) {
                    if (this.cart[index].qty > 1) {
                        this.cart[index].qty--;
                    } else {
                        this.cart.splice(index, 1);
                    }
                    this.recalcCoupon();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.recalcCoupon();
                },

                clearCart() {
                    this.cart = [];
                    this.discountAmount = 0;
                    this.couponCode = '';
                    this.couponMessage = '';
                },

                async applyCoupon() {
                    if (!this.couponCode.trim()) return;

                    try {
                        const response = await fetch('{{ route('pos.validate-coupon') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                code: this.couponCode,
                                subtotal: this.subtotal
                            })
                        });

                        const data = await response.json();
                        if (data.valid) {
                            this.discountAmount = parseFloat(data.discount);
                            this.couponValid = true;
                            this.couponMessage = data.message;
                        } else {
                            this.discountAmount = 0;
                            this.couponValid = false;
                            this.couponMessage = data.message;
                        }
                    } catch (e) {
                        this.couponMessage = 'Failed to validate coupon.';
                        this.couponValid = false;
                    }
                },

                recalcCoupon() {
                    if (this.couponValid && this.couponCode) {
                        this.applyCoupon();
                    }
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.cashTendered = this.totalAmount;
                    this.showPaymentModal = true;
                },

                async submitOrder() {
                    if (this.cart.length === 0) return;
                    this.isProcessing = true;

                    const payload = {
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        coupon_code: this.couponCode,
                        payment_method: this.paymentMethod,
                        transaction_id: this.transactionId,
                        paid_amount: this.totalAmount,
                        items: this.cart.map(i => ({ food_id: i.id, quantity: i.qty }))
                    };

                    try {
                        const res = await fetch('{{ route('pos.checkout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await res.json();
                        if (result.success) {
                            this.completedOrder = result.order;
                            this.invoiceUrl = result.invoice_url;
                            this.showPaymentModal = false;
                        } else {
                            alert(result.message || 'Error processing checkout.');
                        }
                    } catch (err) {
                        alert('Order failed to submit. Please try again.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                newOrder() {
                    this.completedOrder = null;
                    this.clearCart();
                    this.customerName = '';
                    this.customerPhone = '';
                    this.transactionId = '';
                }
            };
        }
    </script>
</x-layouts::app>
