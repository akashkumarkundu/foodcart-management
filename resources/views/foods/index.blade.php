<x-layouts::app title="Food Menu & Price Management">
    <div x-data="foodPriceManager()" class="space-y-4 sm:space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-[var(--fc-text)] flex items-center gap-2">
                    <span>খাবার ও দাম কাস্টমাইজেশন</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">মোবাইল ফ্রেন্ডলি</span>
                </h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">সহজে মোবাইল থেকে যেকোনো খাবারের দাম, খরচ ও স্ট্যাটাস পরিবর্তন করুন</p>
            </div>

            @if(auth()->user()->isOwner())
                <div class="flex items-center gap-2">
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-semibold text-[var(--fc-text)] hover:bg-[var(--fc-bg)]">
                        <flux:icon name="tag" class="size-4" />
                        <span>ক্যাটাগরি</span>
                    </a>

                    <a href="{{ route('foods.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95">
                        <flux:icon name="plus" class="size-4" />
                        <span>নতুন খাবার যোগ</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Success Toast Notification (AJAX / Livewire) -->
        <div
            x-show="notification.show"
            x-transition
            class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm"
            x-cloak
        >
            <div class="flex items-center gap-2">
                <flux:icon name="check-circle" class="size-5 text-emerald-500 shrink-0" />
                <span x-text="notification.message"></span>
            </div>
            <button type="button" @click="notification.show = false" class="text-emerald-500 hover:text-emerald-700 font-bold text-base">&times;</button>
        </div>

        <!-- Filter & Search Bar -->
        <div class="fc-card p-3.5 sm:p-4 shadow-xs">
            <form method="GET" action="{{ route('foods.index') }}" :class="deviceView === 'mobile' ? 'space-y-2.5' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3'">
                <div>
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">খাবার খুঁজুন</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="যেমন: বার্গার, চা, নুডুলস..."
                        class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">ক্যাটাগরি</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                        <option value="">সব ক্যাটাগরি (All)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->bengali_name ?? $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                        <option value="">সব খাবার</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>🟢 চালু আছে (Active)</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>🔴 বন্ধ আছে (Paused)</option>
                    </select>
                </div>

                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2 pt-1' : 'flex items-end gap-2'">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-xs text-center">
                        ফিল্টার করুন
                    </button>
                    <a href="{{ route('foods.index') }}" class="w-full py-2 px-3 rounded-xl border border-[var(--fc-border)] text-xs font-semibold text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)] text-center">
                        রিসেট
                    </a>
                </div>
            </form>
        </div>

        <!-- Food Grid Cards -->
        <div :class="deviceView === 'mobile' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4'">
            @forelse($foods as $food)
                <div id="food-card-{{ $food->id }}" class="fc-card p-4 shadow-xs flex flex-col justify-between group hover:border-[var(--fc-primary)] transition-all rounded-2xl">
                    <div>
                        <!-- Category Badge & Active Dot -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--fc-primary)] bg-[var(--fc-primary)]/10 px-2.5 py-0.5 rounded-md">
                                {{ $food->category?->bengali_name ?? $food->category?->name ?? 'General' }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <span class="size-2.5 rounded-full {{ $food->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                                <span class="text-[10px] font-bold {{ $food->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500' }}">
                                    {{ $food->is_active ? 'চালু' : 'বন্ধ' }}
                                </span>
                            </div>
                        </div>

                        <!-- Name -->
                        <h3 class="font-extrabold text-base text-[var(--fc-text)] leading-tight">
                            {{ $food->bengali_name ?? $food->name }}
                        </h3>
                        @if($food->bengali_name && $food->bengali_name !== $food->name)
                            <p class="text-xs text-[var(--fc-text-muted)] font-medium mt-0.5">{{ $food->name }}</p>
                        @endif

                        <!-- Price & Margin Box (Updated in real-time) -->
                        <div class="my-3 p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[var(--fc-text-muted)] font-medium">বিক্রয় মূল্য:</span>
                                <span id="selling-price-{{ $food->id }}" class="font-black text-base text-emerald-600 dark:text-emerald-400">
                                    ৳{{ number_format($food->selling_price, 2) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[var(--fc-text-muted)] font-medium">ক্রয়/খরচ মূল্য:</span>
                                <span id="cost-price-{{ $food->id }}" class="font-bold text-[var(--fc-text)]">
                                    ৳{{ number_format($food->cost_price, 2) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1.5 border-t border-[var(--fc-border)]/60 text-[11px] font-bold text-[var(--fc-primary)]">
                                <span>আইটেম প্রতি লাভ:</span>
                                <span id="profit-badge-{{ $food->id }}">
                                    ৳{{ number_format($food->profit_per_item, 2) }} ({{ $food->profit_margin }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Stock & Prep info -->
                        <div class="flex items-center justify-between text-xs text-[var(--fc-text-muted)] mb-3">
                            <span>স্টক: <strong class="{{ $food->is_low_stock ? 'text-red-500 font-bold' : 'text-[var(--fc-text)]' }}">{{ $food->current_stock }} {{ $food->unit }}</strong></span>
                            <span>সময়: <strong>{{ $food->preparation_time }} মিনিট</strong></span>
                        </div>
                    </div>

                    <!-- Owner Action Buttons: Quick Price Edit & More -->
                    <div class="pt-3 border-t border-[var(--fc-border)] space-y-2">
                        @if(auth()->user()->isOwner())
                            <!-- Prominent 1-Tap Price Edit Button -->
                            <button
                                type="button"
                                @click="openPriceModal({
                                    id: {{ $food->id }},
                                    name: '{{ addslashes($food->name) }}',
                                    bengali_name: '{{ addslashes($food->bengali_name ?? $food->name) }}',
                                    selling_price: {{ (float) $food->selling_price }},
                                    cost_price: {{ (float) $food->cost_price }}
                                })"
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-all active:scale-98 shadow-xs"
                            >
                                <flux:icon name="pencil-square" class="size-4 text-emerald-600 dark:text-emerald-400" />
                                <span>💰 দাম পরিবর্তন করুন (Edit Price)</span>
                            </button>
                        @endif

                        <div class="flex items-center justify-between gap-2 pt-1">
                            <form method="POST" action="{{ route('foods.toggle-active', $food) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-colors {{ $food->is_active ? 'text-amber-600 hover:bg-amber-500/10' : 'text-emerald-600 hover:bg-emerald-500/10' }}">
                                    {{ $food->is_active ? '⏸️ সাময়িক বন্ধ' : '▶️ চালু করুন' }}
                                </button>
                            </form>

                            @if(auth()->user()->isOwner())
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('foods.edit', $food) }}" class="p-2 rounded-lg border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:text-[var(--fc-primary)]" title="সম্পূর্ণ তথ্য এডিট">
                                        <flux:icon name="pencil" class="size-3.5" />
                                    </a>

                                    <form method="POST" action="{{ route('foods.destroy', $food) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই খাবারটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:text-red-500" title="মুছে ফেলুন">
                                            <flux:icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center fc-card p-6 rounded-2xl">
                    <flux:icon name="cake" class="size-10 text-[var(--fc-text-muted)] mx-auto mb-2 opacity-50" />
                    <p class="text-xs text-[var(--fc-text-muted)]">কোনো খাবার পাওয়া যায়নি।</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-[var(--fc-border)]">
            {{ $foods->links() }}
        </div>

        <!-- Mobile-First Quick Price Customization Modal / Bottom Sheet -->
        <div
            x-show="modalOpen"
            class="fixed inset-0 z-50 overflow-y-auto"
            x-cloak
        >
            <div class="fixed inset-0 bg-black/70 backdrop-blur-xs" @click="modalOpen = false"></div>

            <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div
                    @click.outside="modalOpen = false"
                    class="relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] p-5 sm:p-6 shadow-2xl text-[var(--fc-text)]"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--fc-border)] mb-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--fc-primary)]">দ্রুত দাম আপডেট</span>
                            <h2 class="text-lg font-black text-[var(--fc-text)] leading-tight" x-text="selectedFood.bengali_name || selectedFood.name"></h2>
                            <p class="text-xs text-[var(--fc-text-muted)]" x-text="selectedFood.name"></p>
                        </div>
                        <button type="button" @click="modalOpen = false" class="p-1 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] text-2xl font-bold">&times;</button>
                    </div>

                    <!-- Price Customization Form -->
                    <form @submit.prevent="savePrice()" class="space-y-4">
                        <!-- Selling Price Input -->
                        <div>
                            <label class="block text-xs font-bold text-[var(--fc-text)] mb-1.5 flex items-center justify-between">
                                <span>বিক্রয় মূল্য (Selling Price) *</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">কাস্টমারকে যে দামে বিক্রি করবেন</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-base text-[var(--fc-text-muted)]">৳</span>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    x-model="selectedFood.selling_price"
                                    required
                                    class="w-full pl-9 pr-4 py-3 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-lg font-black text-emerald-600 dark:text-emerald-400 focus:border-[var(--fc-primary)] outline-none"
                                />
                            </div>

                            <!-- Fast Modifier Preset Pills for Mobile -->
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[10px] font-semibold text-[var(--fc-text-muted)]">দ্রুত পরিবর্তন:</span>
                                <button type="button" @click="adjustPrice(5)" class="px-2.5 py-1 rounded-lg bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] hover:border-[var(--fc-primary)]">+৳৫</button>
                                <button type="button" @click="adjustPrice(10)" class="px-2.5 py-1 rounded-lg bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] hover:border-[var(--fc-primary)]">+৳১০</button>
                                <button type="button" @click="adjustPrice(20)" class="px-2.5 py-1 rounded-lg bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] hover:border-[var(--fc-primary)]">+৳২০</button>
                                <button type="button" @click="adjustPrice(-10)" class="px-2.5 py-1 rounded-lg bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] hover:border-[var(--fc-primary)]">-৳১০</button>
                            </div>
                        </div>

                        <!-- Cost Price Input -->
                        <div>
                            <label class="block text-xs font-bold text-[var(--fc-text)] mb-1.5 flex items-center justify-between">
                                <span>ক্রয়/খরচ মূল্য (Cost Price)</span>
                                <span class="text-[var(--fc-text-muted)] font-normal text-[11px]">উপকরণ ও কাঁচামাল খরচ</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-base text-[var(--fc-text-muted)]">৳</span>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    x-model="selectedFood.cost_price"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-sm font-bold text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                                />
                            </div>
                        </div>

                        <!-- Real-time Profit Preview in Modal -->
                        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs flex items-center justify-between">
                            <span class="text-emerald-700 dark:text-emerald-300 font-semibold">আনুমানিক লাভ (Profit):</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">
                                ৳<span x-text="Math.max(0, (selectedFood.selling_price - selectedFood.cost_price)).toFixed(2)"></span>
                            </span>
                        </div>

                        <!-- Buttons -->
                        <div class="pt-2 flex flex-col gap-2">
                            <button
                                type="submit"
                                :disabled="isSaving"
                                class="w-full py-3.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black text-sm flex items-center justify-center gap-2 shadow-lg hover:opacity-95 transition-all active:scale-98 disabled:opacity-50"
                            >
                                <span x-show="!isSaving">✅ নতুন দাম সেভ করুন (Save)</span>
                                <span x-show="isSaving">আপডেট হচ্ছে...</span>
                            </button>

                            <button
                                type="button"
                                @click="modalOpen = false"
                                class="w-full py-2.5 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] font-bold text-xs hover:bg-[var(--fc-bg)]"
                            >
                                বাতিল করুন (Cancel)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Food Price Manager Logic -->
    <script>
        function foodPriceManager() {
            return {
                modalOpen: false,
                isSaving: false,
                selectedFood: {
                    id: null,
                    name: '',
                    bengali_name: '',
                    selling_price: 0,
                    cost_price: 0
                },
                notification: {
                    show: false,
                    message: ''
                },

                openPriceModal(food) {
                    this.selectedFood = {
                        id: food.id,
                        name: food.name,
                        bengali_name: food.bengali_name,
                        selling_price: Number(food.selling_price),
                        cost_price: Number(food.cost_price || 0)
                    };
                    this.modalOpen = true;
                },

                adjustPrice(amount) {
                    this.selectedFood.selling_price = Math.max(0, Number(this.selectedFood.selling_price) + amount);
                },

                async savePrice() {
                    if (!this.selectedFood.id) return;
                    this.isSaving = true;

                    const payload = {
                        selling_price: this.selectedFood.selling_price,
                        cost_price: this.selectedFood.cost_price
                    };

                    try {
                        const response = await fetch(`/foods/${this.selectedFood.id}/price`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update values directly in DOM cards
                            const sellingElem = document.getElementById(`selling-price-${this.selectedFood.id}`);
                            const costElem = document.getElementById(`cost-price-${this.selectedFood.id}`);
                            const profitElem = document.getElementById(`profit-badge-${this.selectedFood.id}`);

                            if (sellingElem) sellingElem.innerText = `৳${Number(data.food.selling_price).toFixed(2)}`;
                            if (costElem) costElem.innerText = `৳${Number(data.food.cost_price).toFixed(2)}`;
                            if (profitElem) profitElem.innerText = `৳${Number(data.food.profit_per_item).toFixed(2)} (${data.food.profit_margin}%)`;

                            this.notification.message = data.message;
                            this.notification.show = true;
                            this.modalOpen = false;

                            setTimeout(() => {
                                this.notification.show = false;
                            }, 4000);
                        } else {
                            alert(data.message || 'দাম আপডেট করতে সমস্যা হয়েছে');
                        }
                    } catch (e) {
                        alert('সার্ভারে যোগাযোগ করতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।');
                    } finally {
                        this.isSaving = false;
                    }
                }
            };
        }
    </script>
</x-layouts::app>
