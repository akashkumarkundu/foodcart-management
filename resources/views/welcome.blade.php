<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>{{ $cartName }} - স্মার্ট ডিজিটাল ফুড কার্ট মেনু</title>
    <meta name="description" content="{{ $cartName }} - রাজশাহী সরকারি মহিলা কলেজ সংলগ্ন ডিজিটাল ফুড কার্ট। অনলাইনে খাবার অর্ডার করুন, ডিসকাউন্ট ভাউচার ও লাইভ ট্র্যাকিং।">

    <!-- Mobile Web App Meta -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#F85606">

    <!-- Fonts: Plus Jakarta Sans & Tiro Bangla -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">

    <!-- Anti-flicker style for Alpine.js & Brand Tokens -->
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --daraz-orange: #F85606;
            --daraz-orange-hover: #E24A00;
            --daraz-orange-light: #FFF2ED;
            --daraz-orange-glow: rgba(248, 86, 6, 0.35);
        }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        .daraz-gradient {
            background: linear-gradient(135deg, #F85606 0%, #FF6000 50%, #FF7700 100%);
        }
        .daraz-badge {
            background: linear-gradient(90deg, #F85606 0%, #FF5000 100%);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        function darazFoodCart() {
            return {
                theme: localStorage.getItem('fc_theme_mode') || 'dark',
                activeTab: 'home',
                searchQuery: '',
                selectedCategory: 'all',
                cart: [],
                checkoutOpen: false,
                successOpen: false,
                trackOpen: false,
                qrModalOpen: false,
                reviewModalOpen: false,
                quickViewOpen: false,
                loginModalOpen: false,
                quickViewFood: null,
                quickViewQty: 1,
                placedOrderNumber: '',
                placedOrderTotal: 0,
                lastOrderNumber: '',
                trackQuery: '',
                trackResult: null,
                trackError: null,
                isSubmitting: false,
                errorMessage: '',
                voucherCode: '',
                appliedVoucher: null,
                voucherError: '',
                voucherSuccess: '',
                isApplyingVoucher: false,
                trackingInterval: null,
                hasPlayedReadySound: false,
                currentUrl: window.location.href,
                activeBanner: 0,
                bannerTimer: null,
                countdownTimer: null,
                countdown: { hours: '02', minutes: '45', seconds: '30' },
                toast: { show: false, message: '' },
                selectedFoodForReview: null,
                selectedFoodNameForReview: '',
                reviewForm: {
                    customer_name: '',
                    customer_phone: '',
                    food_id: null,
                    rating: 5,
                    comment: ''
                },
                isSubmittingReview: false,
                reviewSuccessMsg: '',
                reviewErrorMsg: '',
                orderForm: {
                    customer_name: '',
                    customer_phone: '',
                    order_type: 'dine_in', // 'dine_in' or 'parcel'
                    table_no: '',
                    payment_method: 'cash',
                    transaction_id: '',
                    notes: ''
                },
                vouchersList: @json($coupons ?? []),
                liveTime: '',
                liveDate: '',
                clockInterval: null,
                isCartOpen: {{ $isCartOpen ? 'true' : 'false' }},
                cartStatusInterval: null,

                checkCartStatus() {
                    fetch('{{ route('customer.cart-status') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data && typeof data.is_cart_open !== 'undefined') {
                                this.isCartOpen = !!data.is_cart_open;
                            }
                        })
                        .catch(() => {});
                },

                updateLiveClock() {
                    const now = new Date();
                    let hours = now.getHours();
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    const strHours = String(hours).padStart(2, '0');
                    this.liveTime = `${strHours}:${minutes}:${seconds} ${ampm}`;

                    try {
                        this.liveDate = now.toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' });
                    } catch (e) {
                        this.liveDate = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                    }
                },

                init() {
                    this.applyTheme();
                    this.updateLiveClock();

                    // Start live ticking real-time clock
                    this.clockInterval = setInterval(() => {
                        this.updateLiveClock();
                    }, 1000);

                    // Start live polling cart open/closed status every 3 seconds
                    this.checkCartStatus();
                    this.cartStatusInterval = setInterval(() => {
                        this.checkCartStatus();
                    }, 3000);

                    // Auto-load remembered customer details from browser LocalStorage
                    const savedName = localStorage.getItem('fc_customer_name');
                    const savedPhone = localStorage.getItem('fc_customer_phone');
                    const savedType = localStorage.getItem('fc_order_type');
                    const savedLastOrder = localStorage.getItem('fc_last_order_num');

                    if (savedName) this.orderForm.customer_name = savedName;
                    if (savedPhone) this.orderForm.customer_phone = savedPhone;
                    if (savedType) this.orderForm.order_type = savedType;
                    if (savedLastOrder) {
                        this.lastOrderNumber = savedLastOrder;
                        this.trackQuery = savedLastOrder;
                    }

                    // Start auto-sliding promo banner carousel
                    this.bannerTimer = setInterval(() => {
                        this.activeBanner = (this.activeBanner + 1) % 3;
                    }, 4000);

                    // Start live running Flash Sale countdown timer
                    let totalSeconds = 2 * 3600 + 45 * 60 + 30;
                    this.countdownTimer = setInterval(() => {
                        if (totalSeconds > 0) {
                            totalSeconds--;
                            const h = Math.floor(totalSeconds / 3600);
                            const m = Math.floor((totalSeconds % 3600) / 60);
                            const s = totalSeconds % 60;
                            this.countdown.hours = String(h).padStart(2, '0');
                            this.countdown.minutes = String(m).padStart(2, '0');
                            this.countdown.seconds = String(s).padStart(2, '0');
                        }
                    }, 1000);
                },

                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('fc_theme_mode', this.theme);
                    this.applyTheme();
                    this.showToast(this.theme === 'dark' ? '🌙 ডার্ক মোড চালু হয়েছে' : '☀️ লাইট মোড চালু হয়েছে');
                },

                applyTheme() {
                    if (this.theme === 'dark') {
                        document.documentElement.classList.add('dark');
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.classList.add('light');
                    }
                },

                copyPaymentNumber(num, name) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(num);
                        this.showToast(`📋 ${name} নম্বর (${num}) কপি হয়েছে!`);
                    } else {
                        this.showToast(`নম্বর: ${num}`);
                    }
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },

                matchesSearch(text) {
                    if (!this.searchQuery.trim()) return true;
                    return text.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                },

                getItemQty(foodId) {
                    const item = this.cart.find(i => i.id === foodId);
                    return item ? item.qty : 0;
                },

                addToCart(food, quantity = 1) {
                    const existing = this.cart.find(i => i.id === food.id);
                    if (existing) {
                        existing.qty += quantity;
                    } else {
                        this.cart.push({
                            id: food.id,
                            name: food.name,
                            bengali_name: food.bengali_name || food.name,
                            price: Number(food.price),
                            qty: quantity,
                            unit: food.unit || 'pcs',
                            image: food.image
                        });
                    }
                    this.showToast(`🛒 "${food.bengali_name || food.name}" কার্টে যোগ করা হয়েছে!`);
                },

                decreaseQty(foodId) {
                    const idx = this.cart.findIndex(i => i.id === foodId);
                    if (idx !== -1) {
                        if (this.cart[idx].qty > 1) {
                            this.cart[idx].qty -= 1;
                        } else {
                            this.cart.splice(idx, 1);
                        }
                    }
                },

                removeFromCart(foodId) {
                    this.cart = this.cart.filter(i => i.id !== foodId);
                },

                cartTotalCount() {
                    return this.cart.reduce((total, item) => total + item.qty, 0);
                },

                cartSubtotal() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                cartTotalPrice() {
                    const subtotal = this.cartSubtotal();
                    return Math.max(0, subtotal - this.getDiscountAmount());
                },

                openQuickView(food) {
                    this.quickViewFood = food;
                    this.quickViewQty = 1;
                    this.quickViewOpen = true;
                },

                quickViewAddAndCheckout() {
                    if (!this.quickViewFood) return;
                    this.addToCart(this.quickViewFood, this.quickViewQty);
                    this.quickViewOpen = false;
                    this.checkoutOpen = true;
                },

                async applyVoucherCode(codeToApply = null) {
                    this.voucherError = '';
                    this.voucherSuccess = '';
                    const code = (codeToApply || this.voucherCode || '').trim().toUpperCase();

                    if (!code) {
                        this.voucherError = 'অনুগ্রহ করে ভাউচার কোড লিখুন।';
                        return;
                    }

                    const subtotal = this.cartSubtotal();
                    if (subtotal === 0) {
                        this.voucherError = 'ভাউচার ব্যবহার করতে আগে কার্টে খাবার যোগ করুন।';
                        return;
                    }

                    this.isApplyingVoucher = true;

                    try {
                        const res = await fetch('{{ route("customer.coupon") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                code: code,
                                subtotal: subtotal
                            })
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.appliedVoucher = {
                                code: data.coupon.code,
                                discount: Number(data.coupon.discount),
                                description: data.coupon.description
                            };
                            this.voucherCode = data.coupon.code;
                            this.voucherSuccess = data.message;
                            this.showToast(data.message);
                        } else {
                            this.voucherError = data.message || 'ভাউচারটি প্রযোজ্য নয়।';
                        }
                    } catch (e) {
                        this.voucherError = 'ভাউচার যাচাই করতে সমস্যা হয়েছে।';
                    } finally {
                        this.isApplyingVoucher = false;
                    }
                },

                collectVoucher(coupon) {
                    this.voucherCode = coupon.code;
                    if (this.cart.length > 0) {
                        this.applyVoucherCode(coupon.code);
                    } else {
                        this.showToast(`🎟️ "${coupon.code}" ভাউচার সংগৃহীত! কার্টে খাবার যোগ করলে ডিসকাউন্ট পাবেন।`);
                    }
                },

                removeVoucher() {
                    this.appliedVoucher = null;
                    this.voucherCode = '';
                    this.voucherError = '';
                    this.voucherSuccess = '';
                },

                getDiscountAmount() {
                    if (!this.appliedVoucher) return 0;
                    return Number(this.appliedVoucher.discount || 0);
                },

                addNoteChip(text) {
                    if (!this.orderForm.notes) {
                        this.orderForm.notes = text;
                    } else if (!this.orderForm.notes.includes(text)) {
                        this.orderForm.notes = `${this.orderForm.notes}, ${text}`;
                    }
                },

                playChime(type = 'success') {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        if (ctx.state === 'suspended') ctx.resume();

                        const playTone = (freq, start, dur) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
                            gain.gain.setValueAtTime(0.3, ctx.currentTime + start);
                            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + dur);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(ctx.currentTime + start);
                            osc.stop(ctx.currentTime + start + dur);
                        };

                        if (type === 'success') {
                            playTone(523.25, 0, 0.2); // C5
                            playTone(659.25, 0.15, 0.2); // E5
                            playTone(783.99, 0.3, 0.4); // G5
                        } else if (type === 'ready') {
                            playTone(659.25, 0, 0.25);
                            playTone(880.00, 0.2, 0.5);
                        }
                    } catch (e) {
                        console.log('Audio chime not allowed:', e);
                    }
                },

                async submitOrder() {
                    if (this.cart.length === 0) return;

                    if (!this.orderForm.customer_name.trim()) {
                        this.errorMessage = 'অনুগ্রহ করে আপনার নাম লিখুন।';
                        return;
                    }
                    if (!this.orderForm.customer_phone.trim()) {
                        this.errorMessage = 'অনুগ্রহ করে মোবাইল নম্বর লিখুন।';
                        return;
                    }
                    if (this.orderForm.order_type === 'dine_in' && !this.orderForm.table_no.trim()) {
                        this.errorMessage = 'অনুগ্রহ করে বসে খাওয়ার জন্য আপনার টেবিল নম্বর দিন (যেমন: টেবিল ১ বা কাউন্টার)।';
                        return;
                    }
                    if ((this.orderForm.payment_method === 'bkash' || this.orderForm.payment_method === 'nagad') && !this.orderForm.transaction_id.trim()) {
                        this.errorMessage = 'অনুগ্রহ করে সফলভাবে পেমেন্ট করার পর প্রাপ্ত TrxID / Transaction ID লিখুন।';
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    // Save customer details in browser localStorage
                    localStorage.setItem('fc_customer_name', this.orderForm.customer_name.trim());
                    localStorage.setItem('fc_customer_phone', this.orderForm.customer_phone.trim());
                    localStorage.setItem('fc_order_type', this.orderForm.order_type);

                    const payload = {
                        customer_name: this.orderForm.customer_name.trim(),
                        customer_phone: this.orderForm.customer_phone.trim(),
                        order_type: this.orderForm.order_type,
                        table_no: this.orderForm.order_type === 'dine_in' ? (this.orderForm.table_no.trim() || null) : null,
                        coupon_code: this.appliedVoucher ? this.appliedVoucher.code : null,
                        payment_method: this.orderForm.payment_method,
                        transaction_id: this.orderForm.transaction_id.trim() || null,
                        notes: this.orderForm.notes.trim() || null,
                        items: this.cart.map(item => ({
                            food_id: item.id,
                            quantity: item.qty
                        }))
                    };

                    try {
                        const res = await fetch('{{ route("customer.order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.placedOrderNumber = data.order_number;
                            this.placedOrderTotal = data.total_amount;
                            this.lastOrderNumber = data.order_number;
                            localStorage.setItem('fc_last_order_num', data.order_number);

                            this.cart = [];
                            this.appliedVoucher = null;
                            this.voucherCode = '';
                            this.checkoutOpen = false;
                            this.successOpen = true;

                            this.playChime('success');
                        } else {
                            this.errorMessage = data.message || 'অর্ডার করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                        }
                    } catch (e) {
                        this.errorMessage = 'সার্ভারে যোগাযোগ করা যায়নি। অনুগ্রহ করে ইন্টারনেট কানেকশন চেক করুন।';
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                openReviewModal(foodId = null, foodName = '') {
                    this.selectedFoodForReview = foodId;
                    this.selectedFoodNameForReview = foodName;
                    this.reviewForm.food_id = foodId;
                    this.reviewForm.customer_name = this.orderForm.customer_name || '';
                    this.reviewForm.customer_phone = this.orderForm.customer_phone || '';
                    this.reviewForm.rating = 5;
                    this.reviewForm.comment = '';
                    this.reviewErrorMsg = '';
                    this.reviewSuccessMsg = '';
                    this.reviewModalOpen = true;
                },

                async submitReview() {
                    if (!this.reviewForm.customer_name.trim()) {
                        this.reviewErrorMsg = 'অনুগ্রহ করে আপনার নাম লিখুন।';
                        return;
                    }
                    if (!this.reviewForm.comment.trim()) {
                        this.reviewErrorMsg = 'অনুগ্রহ করে কিছু মন্তব্য বা রিভিউ লিখুন।';
                        return;
                    }

                    this.isSubmittingReview = true;
                    this.reviewErrorMsg = '';

                    try {
                        const res = await fetch('{{ route("customer.review") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.reviewForm)
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.reviewSuccessMsg = 'ধন্যবাদ! আপনার মূল্যবান রিভিউটি সফলভাবে প্রকাশিত হয়েছে।';
                            setTimeout(() => {
                                this.reviewModalOpen = false;
                                window.location.reload();
                            }, 1500);
                        } else {
                            this.reviewErrorMsg = data.message || 'রিভিউ সাবমিট করতে সমস্যা হয়েছে।';
                        }
                    } catch (e) {
                        this.reviewErrorMsg = 'সার্ভার যোগাযোগ ত্রুটি। অনুগ্রহ করে আবার চেষ্টা করুন।';
                    } finally {
                        this.isSubmittingReview = false;
                    }
                },

                async trackOrder() {
                    const q = (this.trackQuery || '').trim();
                    if (!q) {
                        this.trackError = 'অর্ডার নম্বর বা মোবাইল নম্বর দিন।';
                        return;
                    }

                    this.trackError = null;

                    try {
                        const res = await fetch(`{{ route('customer.track') }}?query=${encodeURIComponent(q)}`);
                        const data = await res.json();

                        if (data.found) {
                            this.trackResult = data.order;
                            if (this.trackResult.status === 'ready' && !this.hasPlayedReadySound) {
                                this.playChime('ready');
                                this.hasPlayedReadySound = true;
                            }
                        } else {
                            this.trackResult = null;
                            this.trackError = data.message || 'কোনো অর্ডার খুঁজে পাওয়া যায়নি।';
                        }
                    } catch (e) {
                        this.trackError = 'সার্ভার থেকে ট্র্যাকিং তথ্য আনা যায়নি।';
                    }
                }
            };
        }
    </script>
</head>

<body :class="theme === 'light' ? 'bg-slate-100 text-slate-800' : 'bg-slate-900 text-slate-100'" class="font-['Plus_Jakarta_Sans',sans-serif] antialiased selection:bg-[#F85606] selection:text-white min-h-screen transition-colors duration-200">

    <!-- Alpine App Root -->
    <div x-data="darazFoodCart()" x-init="init()" :class="theme === 'light' ? 'bg-slate-200/50 text-slate-800' : 'bg-slate-950 text-slate-100'" class="relative min-h-screen pb-24 transition-colors">

        <!-- Mobile Container Frame (Native App Feel) -->
        <div :class="theme === 'light' ? 'bg-white border-slate-200 shadow-xl' : 'bg-slate-900 border-slate-800 shadow-2xl'" class="max-w-md mx-auto min-h-screen relative border-x transition-colors">

            <!-- ========================================================= -->
            <!-- 1. Sticky Top Header with Location & Search Bar           -->
            <!-- ========================================================= -->
            <header :class="theme === 'light' ? 'bg-white/95 border-slate-200 shadow-xs' : 'bg-slate-900/95 border-slate-800/80 shadow-md'" class="sticky top-0 z-40 backdrop-blur-md border-b transition-colors">
                <!-- Location Bar -->
                <div :class="theme === 'light' ? 'border-slate-200 bg-slate-50/80' : 'border-slate-800/50 bg-slate-900/50'" class="px-3.5 pt-2 pb-1.5 flex items-center justify-between text-[11px] border-b">
                    <div class="flex items-center gap-1.5 font-semibold truncate" :class="theme === 'light' ? 'text-slate-700' : 'text-slate-300'">
                        <span class="text-[#F85606] text-xs shrink-0">📍</span>
                        <span class="truncate">{{ $cartAddress }}</span>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <!-- Theme Toggle (Brightness / Darkness) -->
                        <button
                            type="button"
                            @click="toggleTheme()"
                            :class="theme === 'light' ? 'bg-slate-200 text-slate-700 border-slate-300' : 'bg-slate-800 text-amber-300 border-slate-700'"
                            class="px-1.5 py-0.5 rounded-lg border text-[10px] font-bold flex items-center gap-1 transition-colors"
                            :title="theme === 'light' ? 'ডার্ক মোড চালু করুন' : 'লাইট মোড চালু করুন'"
                        >
                            <span x-text="theme === 'light' ? '🌙 ডার্ক' : '☀️ লাইট'"></span>
                        </button>

                        <button
                            type="button"
                            @click="qrModalOpen = true"
                            class="text-[10px] text-[#F85606] font-bold hover:underline flex items-center gap-0.5"
                        >
                            <span>📱 QR</span>
                        </button>
                        <span :class="theme === 'light' ? 'text-slate-300' : 'text-slate-700'">|</span>

                        @auth
                            <a
                                href="{{ auth()->user()->isOwner() ? route('dashboard') : route('cartboy.index') }}"
                                class="text-[10px] text-emerald-500 font-bold hover:underline flex items-center gap-0.5"
                            >
                                <span>{{ auth()->user()->isOwner() ? '👑 ওনার' : '🧑‍🍳 কাউন্টার' }}</span>
                            </a>
                            <button
                                type="button"
                                @click="loginModalOpen = true"
                                class="text-[9px] text-slate-400 hover:text-[#F85606]"
                                title="লগইন সুইচ / প্রোফাইল"
                            >⚙️</button>
                        @else
                            <button
                                type="button"
                                @click="loginModalOpen = true"
                                class="text-[10px] text-amber-500 font-bold hover:underline flex items-center gap-0.5"
                            >
                                <span>🧑‍🍳 স্টাফ লগইন</span>
                            </button>
                        @endauth
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- Food Cart Brand Identity Bar (Logo, Name & Live Clock)   -->
                <!-- ========================================================= -->
                @php
                    $cartNameBengali = $cartName;
                    $cartNameEnglish = '';
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $cartName, $matches)) {
                        $cartNameBengali = trim($matches[1]);
                        $cartNameEnglish = trim($matches[2]);
                    } elseif (preg_match('/([a-zA-Z\s\'-]+)/u', $cartName, $enMatch) && preg_match('/([\x{0980}-\x{09FF}\s]+)/u', $cartName, $bnMatch)) {
                        $cartNameBengali = trim($bnMatch[1]);
                        $cartNameEnglish = trim($enMatch[1]);
                    }
                @endphp
                <div :class="theme === 'light' ? 'bg-gradient-to-r from-orange-50/90 via-white to-amber-50/70 border-slate-200' : 'bg-gradient-to-r from-orange-950/40 via-slate-900 to-slate-950 border-slate-800/80'" class="px-3.5 py-2.5 flex items-start justify-between gap-2 border-b transition-colors">
                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                        <!-- Innovative Glowing Food Cart Vector Logo -->
                        <a href="{{ route('home') }}" class="relative group shrink-0 mt-0.5" title="{{ $cartName }}">
                            <div class="size-11 rounded-2xl bg-gradient-to-br from-[#F85606] via-orange-500 to-amber-500 p-0.5 shadow-lg shadow-[#F85606]/30 group-hover:scale-105 transition-transform">
                                <div :class="theme === 'light' ? 'bg-white' : 'bg-slate-950'" class="w-full h-full rounded-[14px] flex items-center justify-center p-1 overflow-hidden">
                                    <img src="{{ asset('images/foodcart-logo.svg') }}" alt="{{ $cartName }} Logo" class="w-full h-full object-contain" />
                                </div>
                            </div>
                            <!-- Live Status Radar Dot -->
                            <span class="absolute -bottom-0.5 -right-0.5 size-3.5 rounded-full bg-emerald-500 border-2" :class="theme === 'light' ? 'border-white' : 'border-slate-900'" title="লাইভ সক্রিয়">
                                <span class="size-1.5 rounded-full bg-white animate-ping"></span>
                            </span>
                        </a>

                        <!-- Food Cart Name (Bengali & English) & Tagline -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h1 :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="font-black text-sm sm:text-base tracking-tight leading-snug">
                                    {{ $cartNameBengali }}
                                </h1>
                                <span class="px-1.5 py-0.2 rounded-md bg-[#F85606]/15 border border-[#F85606]/30 text-[#F85606] font-black text-[9px] uppercase shrink-0">
                                    অফিসিয়াল
                                </span>
                            </div>

                            @if($cartNameEnglish)
                                <div class="text-[11px] font-extrabold text-[#F85606] tracking-wide leading-tight mt-0.5 flex items-center gap-1">
                                    <span class="text-[10px]">🍔</span>
                                    <span>{{ $cartNameEnglish }}</span>
                                </div>
                            @elseif($cartNameBengali !== $cartName)
                                <div class="text-[11px] font-extrabold text-[#F85606] tracking-wide leading-tight mt-0.5">
                                    {{ $cartName }}
                                </div>
                            @endif

                            <p :class="theme === 'light' ? 'text-slate-600' : 'text-slate-400'" class="text-[10px] font-medium truncate flex items-center gap-1 mt-0.5">
                                <span>📍 {{ $cartAddress }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Live Real-Time Digital Clock Widget -->
                    <div class="shrink-0 text-end pl-1 pt-0.5">
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-xl shadow-2xs" :class="theme === 'light' ? 'bg-slate-100/90 border border-slate-200 text-slate-800' : 'bg-slate-950/90 border border-slate-800 text-amber-300'">
                            <span class="size-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="font-mono font-black text-xs tracking-wider" x-text="liveTime">00:00:00 PM</span>
                        </div>
                        <div class="text-[9px] font-semibold mt-0.5" :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" x-text="liveDate">
                            {{ now()->format('d M, Y') }}
                        </div>
                    </div>
                </div>

                <!-- Dynamic Reactive Cart Open / Closed Live Status Banner -->
                <div x-show="isCartOpen" class="px-3.5 py-1.5 bg-emerald-500/15 border-b border-emerald-500/30 flex items-center justify-between text-[11px] transition-all duration-300">
                    <div class="flex items-center gap-1.5 text-emerald-500 dark:text-emerald-400 font-bold">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>🟢 কার্ট খোলা আছে (লাইভ অর্ডার নেওয়া হচ্ছে)</span>
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-300 font-semibold">কাউন্টার চালু</span>
                </div>

                <div x-show="!isCartOpen" class="px-3.5 py-2 bg-gradient-to-r from-red-600/30 via-red-500/20 to-amber-500/20 border-b border-red-500/40 flex items-center justify-between text-[11px] transition-all duration-300" x-cloak>
                    <div class="flex items-center gap-1.5 text-red-400 font-bold">
                        <span class="text-sm">🔴</span>
                        <span>দোকান সাময়িকভাবে বন্ধ আছে (বিকাল ৪টায় খুলবে)</span>
                    </div>
                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-red-500/30 text-red-300 font-black tracking-wider uppercase">অর্ডার স্থগিত</span>
                </div>

                <!-- Search Row -->
                <div class="px-3 py-2 flex items-center gap-2.5">
                    <!-- Brand Logo Thumbnail -->
                    <a href="{{ route('home') }}" class="shrink-0 flex items-center gap-1" title="{{ $cartName }}">
                        <div class="size-8 rounded-xl bg-gradient-to-br from-[#F85606] to-amber-500 p-0.5 shadow-md shadow-[#F85606]/20">
                            <div :class="theme === 'light' ? 'bg-white' : 'bg-slate-950'" class="w-full h-full rounded-[10px] flex items-center justify-center p-0.5 overflow-hidden">
                                <img src="{{ asset('images/foodcart-logo.svg') }}" alt="Logo" class="w-full h-full object-contain" />
                            </div>
                        </div>
                    </a>

                    <!-- Search Box -->
                    <div class="relative flex-1">
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="ফুডকার্টে খাবার খুঁজুন (যেমন: বার্গার, চা, নুডুলস)..."
                            :class="theme === 'light' ? 'bg-slate-100 border-slate-300 text-slate-800 placeholder:text-slate-400' : 'bg-slate-950 border-slate-800 text-white placeholder:text-slate-500'"
                            class="w-full pl-8 pr-7 py-2 rounded-xl border focus:border-[#F85606] text-xs outline-none transition-colors"
                        />
                        <svg class="size-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button
                            type="button"
                            x-show="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-white text-xs"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Cart Trigger Icon Button with Badge -->
                    <button
                        type="button"
                        @click="checkoutOpen = true"
                        class="relative p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-[#F85606] text-slate-300 hover:text-[#F85606] transition-colors shrink-0"
                        title="কার্ট দেখুন"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span
                            x-show="cartTotalCount() > 0"
                            class="absolute -top-1 -right-1 size-4.5 rounded-full bg-[#F85606] text-white text-[10px] font-black flex items-center justify-center animate-pulse"
                            x-text="cartTotalCount()"
                        ></span>
                    </button>
                </div>

                <!-- Live Status Notice (if cart is closed) -->
                <div x-show="!isCartOpen" class="px-3 py-1.5 bg-red-500/20 border-t border-red-500/40 text-red-300 text-[11px] font-bold flex items-center justify-between transition-all duration-300" x-cloak>
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-red-500 animate-ping"></span>
                        <span>কার্ট সাময়িক বন্ধ আছে। এখন অর্ডার নেওয়া হচ্ছে না।</span>
                    </div>
                    <span class="text-[9px] bg-red-950 px-1.5 py-0.2 rounded border border-red-800/60">বন্ধ</span>
                </div>
            </header>

            <!-- ========================================================= -->
            <!-- 2. Toast Notification Floating Banner                     -->
            <!-- ========================================================= -->
            <div
                x-show="toast.show"
                x-transition
                class="fixed top-16 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-2xl bg-slate-900 border border-[#F85606] text-white text-xs font-bold shadow-2xl flex items-center gap-2 pointer-events-none"
                x-cloak
            >
                <span class="text-sm">⚡</span>
                <span x-text="toast.message"></span>
            </div>

            <!-- ========================================================= -->
            <!-- 3. Daraz Auto-Sliding Promotional Hero Banner             -->
            <!-- ========================================================= -->
            <div class="p-3 pb-1">
                <div class="relative rounded-2xl overflow-hidden shadow-lg border border-slate-800 bg-gradient-to-r from-orange-950 via-slate-900 to-slate-950">
                    <!-- Slide 1 -->
                    <div x-show="activeBanner === 0" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" class="p-4 flex items-center justify-between min-h-[110px]">
                        <div class="space-y-1 max-w-[65%]">
                            <span class="px-2 py-0.5 rounded-full daraz-badge text-[9px] font-black text-white uppercase tracking-wider">🔥 মেগা ডিল</span>
                            <h3 class="text-sm font-black text-white leading-tight">ঝাল বিফ নাগা বার্গার স্পেশাল!</h3>
                            <p class="text-[10px] text-slate-300">আজকের অর্ডারে পাচ্ছেন নিশ্চিত ২০% ডিসকাউন্ট</p>
                        </div>
                        <div class="text-4xl animate-bounce">🍔</div>
                    </div>

                    <!-- Slide 2 -->
                    <div x-show="activeBanner === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" class="p-4 flex items-center justify-between min-h-[110px]" x-cloak>
                        <div class="space-y-1 max-w-[65%]">
                            <span class="px-2 py-0.5 rounded-full bg-blue-600 text-[9px] font-black text-white uppercase tracking-wider">⚡ দ্রুত পার্সেল</span>
                            <h3 class="text-sm font-black text-white leading-tight">গরম গরম পার্সেল ও টেকওয়ে!</h3>
                            <p class="text-[10px] text-slate-300">অর্ডার দিলে মাত্র ১৫ মিনিটে প্যাকেট রেডি</p>
                        </div>
                        <div class="text-4xl animate-pulse">🛍️</div>
                    </div>

                    <!-- Slide 3 -->
                    <div x-show="activeBanner === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" class="p-4 flex items-center justify-between min-h-[110px]" x-cloak>
                        <div class="space-y-1 max-w-[65%]">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-[9px] font-black text-white uppercase tracking-wider">🎟️ ভাউচার অফার</span>
                            <h3 class="text-sm font-black text-white leading-tight">কুপন কোড: FOOD50</h3>
                            <p class="text-[10px] text-slate-300">৳৪০০ বা বেশি অর্ডারে ইনস্ট্যান্ট ৳৫০ ছাড়</p>
                        </div>
                        <div class="text-4xl">🎁</div>
                    </div>

                    <!-- Slide Dots -->
                    <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-1.5">
                        <button type="button" @click="activeBanner = 0" class="size-1.5 rounded-full transition-all" :class="activeBanner === 0 ? 'bg-[#F85606] w-4' : 'bg-slate-600'"></button>
                        <button type="button" @click="activeBanner = 1" class="size-1.5 rounded-full transition-all" :class="activeBanner === 1 ? 'bg-[#F85606] w-4' : 'bg-slate-600'"></button>
                        <button type="button" @click="activeBanner = 2" class="size-1.5 rounded-full transition-all" :class="activeBanner === 2 ? 'bg-[#F85606] w-4' : 'bg-slate-600'"></button>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 4. Daraz Circular Channel Icons (Quick Category Shortcuts) -->
            <!-- ========================================================= -->
            <div class="px-3 py-2">
                <div class="grid grid-cols-6 gap-1.5 text-center">
                    <!-- 1. Flash Deals -->
                    <button
                        type="button"
                        @click="selectedCategory = 'all'; document.getElementById('flash-sale-section')?.scrollIntoView({ behavior: 'smooth' })"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-amber-500/15 border-amber-500/30' : 'bg-gradient-to-br from-amber-500/20 to-orange-500/10 border-amber-500/30'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-[#F85606] transition-all">
                            ⚡
                        </div>
                        <span :class="theme === 'light' ? 'text-slate-900' : 'text-slate-200'" class="text-[10px] font-black mt-1 leading-tight transition-colors">ফ্ল্যাশ সেল</span>
                    </button>

                    <!-- 2. Burgers -->
                    <button
                        type="button"
                        @click="selectedCategory = 'fast-food'"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-orange-500/15 border-orange-500/30' : 'bg-slate-950 border-slate-800'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-[#F85606] transition-all">
                            🍔
                        </div>
                        <span :class="theme === 'light' ? 'text-slate-900' : 'text-slate-200'" class="text-[10px] font-black mt-1 leading-tight transition-colors">বার্গার</span>
                    </button>

                    <!-- 3. Tea & Coffee -->
                    <button
                        type="button"
                        @click="selectedCategory = 'tea-coffee'"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-amber-600/15 border-amber-600/30' : 'bg-slate-950 border-slate-800'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-[#F85606] transition-all">
                            ☕
                        </div>
                        <span :class="theme === 'light' ? 'text-slate-900' : 'text-slate-200'" class="text-[10px] font-black mt-1 leading-tight transition-colors">চা-কফি</span>
                    </button>

                    <!-- 4. Parcel Combos -->
                    <button
                        type="button"
                        @click="orderForm.order_type = 'parcel'; checkoutOpen = true"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-blue-500/15 border-blue-500/30' : 'bg-blue-500/15 border-blue-500/30'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-blue-400 transition-all">
                            🛍️
                        </div>
                        <span :class="theme === 'light' ? 'text-blue-900' : 'text-blue-300'" class="text-[10px] font-black mt-1 leading-tight transition-colors">পার্সেল</span>
                    </button>

                    <!-- 5. Vouchers -->
                    <button
                        type="button"
                        @click="document.getElementById('vouchers-section')?.scrollIntoView({ behavior: 'smooth' })"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-pink-500/15 border-pink-500/30' : 'bg-pink-500/15 border-pink-500/30'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-pink-400 transition-all">
                            🎟️
                        </div>
                        <span :class="theme === 'light' ? 'text-pink-900' : 'text-pink-300'" class="text-[10px] font-black mt-1 leading-tight transition-colors">ভাউচার</span>
                    </button>

                    <!-- 6. Top Rated -->
                    <button
                        type="button"
                        @click="document.getElementById('reviews-section')?.scrollIntoView({ behavior: 'smooth' })"
                        class="flex flex-col items-center group active:scale-95 transition-transform"
                    >
                        <div :class="theme === 'light' ? 'bg-amber-500/15 border-amber-500/30' : 'bg-amber-500/15 border-amber-500/30'" class="size-11 rounded-2xl border flex items-center justify-center text-lg shadow-xs group-hover:border-amber-400 transition-all">
                            ⭐
                        </div>
                        <span :class="theme === 'light' ? 'text-amber-900' : 'text-amber-300'" class="text-[10px] font-black mt-1 leading-tight transition-colors">রিভিউ</span>
                    </button>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 5. Daraz Flash Sale Section with Countdown Timer          -->
            <!-- ========================================================= -->
            <div id="flash-sale-section" class="p-3 pt-2">
                <div :class="theme === 'light' ? 'bg-gradient-to-b from-orange-100/50 via-white to-slate-50 border-orange-200 shadow-sm' : 'bg-gradient-to-b from-orange-950/40 via-slate-900 to-slate-900 border-[#F85606]/30 shadow-lg'" class="rounded-3xl border p-3">
                    <!-- Flash Header with Clock -->
                    <div :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800/80'" class="flex items-center justify-between pb-2.5 border-b">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-lg daraz-badge text-[10px] font-black text-white flex items-center gap-1 shadow-sm">
                                <span>⚡</span>
                                <span>Flash Sale</span>
                            </span>
                            <!-- Countdown Pill -->
                            <div class="flex items-center gap-1 text-[10px] font-mono font-black text-slate-200">
                                <span :class="theme === 'light' ? 'bg-white border-slate-300' : 'bg-slate-950 border-slate-800'" class="size-5 rounded border flex items-center justify-center text-[#F85606]" x-text="countdown.hours">02</span>
                                <span :class="theme === 'light' ? 'text-slate-600' : 'text-slate-200'">:</span>
                                <span :class="theme === 'light' ? 'bg-white border-slate-300' : 'bg-slate-950 border-slate-800'" class="size-5 rounded border flex items-center justify-center text-[#F85606]" x-text="countdown.minutes">45</span>
                                <span :class="theme === 'light' ? 'text-slate-600' : 'text-slate-200'">:</span>
                                <span :class="theme === 'light' ? 'bg-white border-slate-300' : 'bg-slate-950 border-slate-800'" class="size-5 rounded border flex items-center justify-center text-[#F85606]" x-text="countdown.seconds">30</span>
                            </div>
                        </div>

                        <span class="text-[10px] font-bold text-[#F85606] hover:underline cursor-pointer">
                            সব ডিল &rarr;
                        </span>
                    </div>

                    <!-- Horizontal Flash Cards Swiper -->
                    <div class="pt-3 flex gap-2.5 overflow-x-auto pb-1 scrollbar-none">
                        @foreach($flashSaleFoods as $fItem)
                            <div :class="theme === 'light' ? 'bg-white border-slate-200 shadow-xs' : 'bg-slate-950 border-slate-800 shadow-md'" class="w-32 shrink-0 rounded-2xl border p-2 space-y-1.5 hover:border-[#F85606]/50 transition-all flex flex-col justify-between">
                                <div class="space-y-1">
                                    <!-- Thumbnail with Discount Tag -->
                                    <div class="relative rounded-xl overflow-hidden aspect-square bg-slate-900 flex items-center justify-center">
                                        <img
                                            src="{{ $fItem->image_url }}"
                                            alt="{{ $fItem->name }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'"
                                        />
                                        <span class="absolute top-1 left-1 px-1.5 py-0.2 rounded-md bg-[#F85606] text-white font-black text-[9px] shadow-xs">
                                            -{{ $fItem->flash_discount_pct }}%
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h4 :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="font-bold text-[11px] line-clamp-1">
                                        {{ $fItem->bengali_name ?? $fItem->name }}
                                    </h4>

                                    <!-- Pricing -->
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xs font-black text-[#F85606]">৳{{ (int)$fItem->selling_price }}</span>
                                        <span class="text-[9px] text-slate-500 line-through">৳{{ $fItem->flash_original_price }}</span>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="space-y-0.5">
                                        <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-gradient-to-r from-[#F85606] to-amber-500 h-full rounded-full" style="width: 70%"></div>
                                        </div>
                                        <span class="text-[8px] text-slate-400 block truncate">🔥 {{ $fItem->flash_sold_count }}টি বিক্রি</span>
                                    </div>
                                </div>

                                <!-- Add to cart button -->
                                <button
                                    type="button"
                                    @click="addToCart({ id: {{ $fItem->id }}, name: '{{ addslashes($fItem->name) }}', bengali_name: '{{ addslashes($fItem->bengali_name ?? $fItem->name) }}', price: {{ $fItem->selling_price }}, image: '{{ $fItem->image_url }}', unit: '{{ $fItem->unit }}' })"
                                    class="w-full py-1.5 rounded-xl daraz-gradient hover:brightness-110 text-white font-black text-[10px] active:scale-95 transition-all shadow-xs"
                                >
                                    + কার্টে নিন
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 6. Daraz Collectible Vouchers Section                      -->
            <!-- ========================================================= -->
            @if($coupons->isNotEmpty())
                <div id="vouchers-section" class="px-3 py-2">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">🎟️</span>
                            <h3 :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="text-xs font-black">স্পেশাল ডিসকাউন্ট ভাউচার</h3>
                        </div>
                        <span :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-[10px]">১-ট্যাপে সংগ্রহ করুন</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($coupons as $cp)
                            <div :class="theme === 'light' ? 'bg-gradient-to-r from-orange-50 to-amber-50/70 border-orange-200' : 'bg-gradient-to-r from-orange-950/60 to-slate-900 border-[#F85606]/40'" class="relative rounded-2xl border p-2.5 flex items-center justify-between gap-2 shadow-xs overflow-hidden">
                                <!-- Left punch-hole design -->
                                <div class="space-y-0.5 flex-1 min-w-0">
                                    <div class="flex items-center gap-1">
                                        <span class="font-black text-xs text-[#F85606]">{{ $cp->code }}</span>
                                    </div>
                                    <p :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="text-[10px] font-bold truncate">
                                        {{ $cp->discount_type === 'percentage' ? (int)$cp->discount_value.'% ছাড়' : '৳'.(int)$cp->discount_value.' ছাড়' }}
                                    </p>
                                    <span :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-[8px] block truncate">মিনিমাম অর্ডার ৳{{ (int)$cp->min_order_amount }}</span>
                                </div>

                                <button
                                    type="button"
                                    @click="collectVoucher({ code: '{{ $cp->code }}' })"
                                    class="shrink-0 px-2.5 py-1 rounded-xl daraz-gradient text-white text-[10px] font-black shadow-xs active:scale-90 transition-transform"
                                >
                                    সংগ্রহ
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ========================================================= -->
            <!-- 7. Category Filter Tabs                                   -->
            <!-- ========================================================= -->
            <div :class="theme === 'light' ? 'bg-white/95 border-slate-200 shadow-xs' : 'bg-slate-900/95 border-slate-800/80'" class="sticky top-[108px] z-30 backdrop-blur border-y px-3 py-2 transition-colors">
                <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                    <button
                        type="button"
                        @click="selectedCategory = 'all'"
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 transition-all"
                        :class="selectedCategory === 'all' ? 'daraz-gradient text-white font-black shadow-sm' : (theme === 'light' ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-950 font-bold' : 'bg-slate-950 border border-slate-800 text-slate-300 hover:text-white font-bold')"
                    >
                        সব খাবার
                    </button>

                    @foreach($categories as $category)
                        <button
                            type="button"
                            @click="selectedCategory = '{{ $category->slug }}'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 transition-all"
                            :class="selectedCategory === '{{ $category->slug }}' ? 'daraz-gradient text-white font-black shadow-sm' : (theme === 'light' ? 'bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-950 font-bold' : 'bg-slate-950 border border-slate-800 text-slate-300 hover:text-white font-bold')"
                        >
                            {{ $category->bengali_name ?? $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 8. Daraz 2-Column Mobile-First Product Catalog Grid        -->
            <!-- ========================================================= -->
            <div class="p-3">
                <div class="flex items-center justify-between mb-2.5">
                    <h3 :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="text-xs font-black flex items-center gap-1.5">
                        <span>🍔</span>
                        <span>সকল খাবারের মেনু তালিকা</span>
                    </h3>
                    <span :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-[10px]">২-কলাম গ্রিড ভিউ</span>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    @foreach($categories as $category)
                        @foreach($category->activeFoods as $food)
                            <div
                                x-show="(selectedCategory === 'all' || selectedCategory === '{{ $category->slug }}') && matchesSearch('{{ addslashes($food->name . ' ' . ($food->bengali_name ?? '')) }}')"
                                :class="theme === 'light' ? 'bg-white border-slate-200 shadow-xs hover:border-[#F85606]' : 'bg-slate-950 border-slate-800 shadow-md hover:border-[#F85606]/60'"
                                class="rounded-2xl border p-2.5 flex flex-col justify-between transition-all group"
                            >
                                <div class="space-y-2">
                                    <!-- Image Container with Discount Tag & Click for Quick View -->
                                    <div
                                        @click="openQuickView({ id: {{ $food->id }}, name: '{{ addslashes($food->name) }}', bengali_name: '{{ addslashes($food->bengali_name ?? $food->name) }}', price: {{ $food->selling_price }}, image: '{{ $food->image_url }}', description: '{{ addslashes($food->description ?? 'খাঁটি উপাদানে তৈরি ফ্রেশ ও গরম খাবার।') }}', unit: '{{ $food->unit }}', rating: '{{ $food->average_rating ?? 4.8 }}' })"
                                        class="relative rounded-xl overflow-hidden aspect-square bg-slate-900 cursor-pointer"
                                    >
                                        <img
                                            src="{{ $food->image_url }}"
                                            alt="{{ $food->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            onerror="this.src='https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80'"
                                        />
                                        <!-- Daraz Flame Discount Ribbon -->
                                        <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-md daraz-badge text-white font-black text-[9px] shadow-sm">
                                            -20%
                                        </span>
                                        <!-- Category Tag -->
                                        <span class="absolute bottom-1 left-1 px-1.5 py-0.2 rounded bg-black/60 backdrop-blur-xs text-[8px] text-slate-200">
                                            {{ $category->bengali_name ?? $category->name }}
                                        </span>
                                    </div>

                                    <!-- Bengali Food Name & English Subtitle -->
                                    <div>
                                        <h4 :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="font-black text-xs line-clamp-1">
                                            {{ $food->bengali_name ?? $food->name }}
                                        </h4>
                                        <p :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-[10px] line-clamp-1">
                                            {{ $food->name }}
                                        </p>
                                    </div>

                                    <!-- Rating & Reviews Bar -->
                                    <div class="flex items-center gap-1 text-[10px]">
                                        <div class="text-amber-400 font-bold">
                                            ★ {{ $food->average_rating ?? '4.8' }}
                                        </div>
                                        <span class="text-slate-500 font-medium">({{ $food->reviews->count() ?: 12 }})</span>
                                        <span class="text-[8px] px-1 py-0.2 rounded bg-emerald-950 text-emerald-400 ml-auto font-semibold">ইনস্ট্যান্ট</span>
                                    </div>

                                    <!-- Daraz Pricing Row -->
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm font-black text-[#F85606]">৳{{ (int)$food->selling_price }}</span>
                                        <span class="text-[10px] text-slate-500 line-through">৳{{ round($food->selling_price * 1.25) }}</span>
                                    </div>
                                </div>

                                <!-- Add to Cart or Stepper Pill -->
                                <div class="pt-2">
                                    <template x-if="getItemQty({{ $food->id }}) === 0">
                                        <button
                                            @click="addToCart({ id: {{ $food->id }}, name: '{{ addslashes($food->name) }}', bengali_name: '{{ addslashes($food->bengali_name ?? $food->name) }}', price: {{ $food->selling_price }}, image: '{{ $food->image_url }}', unit: '{{ $food->unit }}' })"
                                            type="button"
                                            :disabled="!{{ $isCartOpen ? 'true' : 'false' }}"
                                            class="w-full py-1.5 rounded-xl border border-[#F85606]/50 bg-[#F85606]/10 hover:bg-[#F85606] text-[#F85606] hover:text-white font-bold text-xs flex items-center justify-center gap-1 active:scale-95 transition-all disabled:opacity-40"
                                        >
                                            <span>+ কার্টে নিন</span>
                                        </button>
                                    </template>

                                    <template x-if="getItemQty({{ $food->id }}) > 0">
                                        <div class="flex items-center justify-between rounded-xl bg-slate-900 border border-[#F85606] px-2 py-1 text-xs">
                                            <button
                                                @click="decreaseQty({{ $food->id }})"
                                                type="button"
                                                class="size-5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-black flex items-center justify-center active:scale-90"
                                            >
                                                -
                                            </button>
                                            <span class="font-black text-white px-1" x-text="getItemQty({{ $food->id }})"></span>
                                            <button
                                                @click="addToCart({ id: {{ $food->id }}, name: '{{ addslashes($food->name) }}', bengali_name: '{{ addslashes($food->bengali_name ?? $food->name) }}', price: {{ $food->selling_price }}, image: '{{ $food->image_url }}', unit: '{{ $food->unit }}' })"
                                                type="button"
                                                class="size-5 rounded-lg daraz-gradient text-white font-black flex items-center justify-center active:scale-90"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 9. Daraz Verified Customer Reviews & Ratings Section      -->
            <!-- ========================================================= -->
            <div id="reviews-section" class="p-3 pt-2">
                <div :class="theme === 'light' ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-950 border-slate-800 shadow-md'" class="rounded-3xl border p-4 space-y-3">
                    <!-- Rating Summary Card -->
                    <div :class="theme === 'light' ? 'border-slate-200' : 'border-slate-800'" class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <div class="flex items-baseline gap-1.5">
                                <span :class="theme === 'light' ? 'text-slate-900' : 'text-white'" class="text-2xl font-black">৪.৮</span>
                                <span :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-xs">/ ৫.০</span>
                            </div>
                            <div class="text-[#F85606] text-xs font-bold">★★★★★</div>
                            <span :class="theme === 'light' ? 'text-slate-600' : 'text-slate-400'" class="text-[10px]">৯৮% কাস্টমার খাবারের প্রশংসা করেছেন</span>
                        </div>

                        <button
                            type="button"
                            @click="openReviewModal()"
                            class="px-3 py-1.5 rounded-xl daraz-gradient hover:brightness-110 text-white text-xs font-bold shadow-xs active:scale-95 transition-transform"
                        >
                            ★ রিভিউ লিখুন
                        </button>
                    </div>

                    <!-- Reviews List -->
                    @if(isset($recentReviews) && $recentReviews->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($recentReviews as $rev)
                                <div :class="theme === 'light' ? 'bg-slate-50 border-slate-200' : 'bg-slate-900 border-slate-800/80'" class="p-2.5 rounded-2xl border text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <div :class="theme === 'light' ? 'text-slate-800' : 'text-slate-200'" class="font-bold flex items-center gap-1.5">
                                            <span>{{ $rev->customer_name }}</span>
                                            @if($rev->food)
                                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-orange-950 text-[#F85606] border border-[#F85606]/30">
                                                    {{ $rev->food->bengali_name ?? $rev->food->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-amber-500 font-black text-[11px]">
                                            {{ str_repeat('★', $rev->rating) }}{{ str_repeat('☆', 5 - $rev->rating) }}
                                        </div>
                                    </div>
                                    <p :class="theme === 'light' ? 'text-slate-700' : 'text-slate-300'" class="text-[11px] font-normal leading-relaxed">
                                        "{{ $rev->comment }}"
                                    </p>
                                    <div :class="theme === 'light' ? 'text-slate-500' : 'text-slate-500'" class="text-[9px]">
                                        {{ $rev->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'" class="text-center py-4 text-xs">
                            এখনো কোনো রিভিউ যোগ করা হয়নি। আপনি প্রথম রিভিউ দিয়ে আমাদের রেটিং দিন!
                        </div>
                    @endif
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 10. Footer Section with Cart & Staff Login Links          -->
            <!-- ========================================================= -->
            <footer class="p-4 text-center text-xs text-slate-500 space-y-2 border-t border-slate-800/80 mt-4">
                <div class="text-[11px] text-slate-400 font-semibold">
                    {{ $footerText }}
                </div>
                <div class="text-[10px]">
                    কার্ট মোবাইল: <a href="tel:{{ $cartPhone }}" class="text-[#F85606] hover:underline font-bold">{{ $cartPhone }}</a>
                </div>
                <div class="text-[10px] text-slate-600 pt-1">
                    Powered by FoodCart Cloud Management • Daraz Edition
                </div>
            </footer>

            <!-- ========================================================= -->
            <!-- 11. Daraz Floating Bottom Cart Pill (Slides Up when Cart>0)-->
            <!-- ========================================================= -->
            <div
                x-show="cartTotalCount() > 0"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-12 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-12 opacity-0"
                class="fixed sm:absolute bottom-16 left-0 right-0 z-40 px-3 py-2 pointer-events-none"
            >
                <div class="max-w-md mx-auto pointer-events-auto">
                    <button
                        @click="checkoutOpen = true"
                        type="button"
                        class="w-full py-3 px-4 rounded-2xl daraz-gradient text-white shadow-xl shadow-[#F85606]/40 flex items-center justify-between font-black active:scale-[0.98] transition-transform border border-white/15"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="size-7 rounded-xl bg-white/20 flex items-center justify-center text-xs font-black" x-text="cartTotalCount()"></span>
                            <span class="text-xs sm:text-sm">খাবার কার্ট দেখুন</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-black" x-text="'৳' + cartTotalPrice()"></span>
                            <span class="text-xs opacity-90">অর্ডার সম্পন্ন করুন &rarr;</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 12. Daraz 5-Tab Fixed Bottom Mobile Navigation Bar        -->
            <!-- ========================================================= -->
            <nav class="fixed sm:absolute bottom-0 left-0 right-0 z-30 bg-slate-950/95 backdrop-blur-md border-t border-slate-800 px-3 py-1.5">
                <div class="max-w-md mx-auto grid grid-cols-5 gap-1 text-center">
                    <!-- Home Tab -->
                    <button
                        type="button"
                        @click="activeTab = 'home'; selectedCategory = 'all'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="flex flex-col items-center justify-center py-1 transition-colors"
                        :class="activeTab === 'home' ? 'text-[#F85606] font-black' : 'text-slate-400 font-medium hover:text-slate-200'"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span class="text-[9px] mt-0.5">হোম</span>
                    </button>

                    <!-- Categories Tab -->
                    <button
                        type="button"
                        @click="activeTab = 'menu'; window.scrollTo({ top: 350, behavior: 'smooth' })"
                        class="flex flex-col items-center justify-center py-1 transition-colors"
                        :class="activeTab === 'menu' ? 'text-[#F85606] font-black' : 'text-slate-400 font-medium hover:text-slate-200'"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span class="text-[9px] mt-0.5">ক্যাটেগরি</span>
                    </button>

                    <!-- Flash Deals Tab -->
                    <button
                        type="button"
                        @click="activeTab = 'deals'; document.getElementById('flash-sale-section')?.scrollIntoView({ behavior: 'smooth' })"
                        class="flex flex-col items-center justify-center py-1 transition-colors"
                        :class="activeTab === 'deals' ? 'text-[#F85606] font-black' : 'text-slate-400 font-medium hover:text-slate-200'"
                    >
                        <span class="text-base leading-none">⚡</span>
                        <span class="text-[9px] mt-0.5">ফ্ল্যাশ সেল</span>
                    </button>

                    <!-- Order Tracking Tab -->
                    <button
                        type="button"
                        @click="activeTab = 'track'; trackOpen = true; if(lastOrderNumber && !trackResult) { trackQuery = lastOrderNumber; trackOrder(); }"
                        class="flex flex-col items-center justify-center py-1 transition-colors relative"
                        :class="activeTab === 'track' ? 'text-[#F85606] font-black' : 'text-slate-400 font-medium hover:text-slate-200'"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="text-[9px] mt-0.5">ট্র্যাক</span>
                        <span x-show="lastOrderNumber" class="absolute top-0.5 right-4 size-2 rounded-full bg-[#F85606] animate-ping"></span>
                    </button>

                    <!-- Cart Tab -->
                    <button
                        type="button"
                        @click="activeTab = 'cart'; checkoutOpen = true"
                        class="flex flex-col items-center justify-center py-1 transition-colors relative"
                        :class="activeTab === 'cart' ? 'text-[#F85606] font-black' : 'text-slate-400 font-medium hover:text-slate-200'"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="text-[9px] mt-0.5">কার্ট</span>
                        <span
                            x-show="cartTotalCount() > 0"
                            class="absolute top-0 right-3.5 px-1.5 py-0.2 rounded-full bg-[#F85606] text-white text-[9px] font-black"
                            x-text="cartTotalCount()"
                        ></span>
                    </button>
                </div>
            </nav>

            <!-- ========================================================= -->
            <!-- 13. MODAL: Product Quick View & Detail Sheet              -->
            <!-- ========================================================= -->
            <div
                x-show="quickViewOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
                x-cloak
            >
                <div
                    @click.outside="quickViewOpen = false"
                    class="w-full max-w-md bg-slate-900 border-t sm:border border-slate-800 rounded-t-[32px] sm:rounded-3xl p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                >
                    <div class="w-12 h-1.5 rounded-full bg-slate-700 mx-auto sm:hidden shrink-0"></div>

                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-md daraz-badge text-white font-black text-[9px]">১০০% সেরা কোয়ালিটি নিশ্চিত</span>
                        <button @click="quickViewOpen = false" class="size-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs flex items-center justify-center">✕</button>
                    </div>

                    <template x-if="quickViewFood">
                        <div class="space-y-3">
                            <div class="rounded-2xl overflow-hidden aspect-video bg-slate-950">
                                <img :src="quickViewFood.image" :alt="quickViewFood.name" class="w-full h-full object-cover" />
                            </div>

                            <div>
                                <h3 class="text-base font-black text-white" x-text="quickViewFood.bengali_name"></h3>
                                <p class="text-xs text-slate-400" x-text="quickViewFood.name"></p>
                            </div>

                            <p class="text-xs text-slate-300 leading-relaxed" x-text="quickViewFood.description"></p>

                            <!-- Pricing -->
                            <div class="flex items-baseline gap-2 pt-1 border-t border-slate-800">
                                <span class="text-xl font-black text-[#F85606]" x-text="'৳' + quickViewFood.price"></span>
                                <span class="text-xs text-slate-500 line-through" x-text="'৳' + Math.round(quickViewFood.price * 1.25)"></span>
                                <span class="text-[10px] font-bold text-emerald-400 ml-auto">ইন স্টক (স্টকে আছে)</span>
                            </div>

                            <!-- Quantity Stepper -->
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs font-bold text-slate-300">পরিমাণ:</span>
                                <div class="flex items-center gap-3 bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5">
                                    <button
                                        type="button"
                                        @click="if(quickViewQty > 1) quickViewQty--"
                                        class="size-6 rounded-lg bg-slate-800 text-white font-black"
                                    >-</button>
                                    <span class="font-black text-sm text-white" x-text="quickViewQty"></span>
                                    <button
                                        type="button"
                                        @click="quickViewQty++"
                                        class="size-6 rounded-lg daraz-gradient text-white font-black"
                                    >+</button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-2 gap-2 pt-3">
                                <button
                                    type="button"
                                    @click="addToCart(quickViewFood, quickViewQty); quickViewOpen = false"
                                    class="py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs"
                                >
                                    কার্টে যোগ করুন
                                </button>
                                <button
                                    type="button"
                                    @click="quickViewAddAndCheckout()"
                                    class="py-3 rounded-2xl daraz-gradient hover:brightness-110 text-white font-black text-xs shadow-md"
                                >
                                    এখনই কিনুন &rarr;
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 14. MODAL: Daraz Slide-Up Cart & Checkout Bottom Sheet    -->
            <!-- ========================================================= -->
            <div
                x-show="checkoutOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-end justify-center"
                x-cloak
            >
                <div
                    @click.outside="checkoutOpen = false"
                    class="w-full max-w-md bg-slate-900 border-t border-slate-800 rounded-t-[32px] p-4 max-h-[92vh] flex flex-col shadow-2xl overflow-hidden"
                >
                    <div class="w-12 h-1.5 rounded-full bg-slate-700 mx-auto mb-3 shrink-0"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="size-7 rounded-xl daraz-gradient flex items-center justify-center font-black text-sm text-white">🛒</span>
                            <h2 class="text-base font-black text-white">খাবার কার্ট ও চেকআউট</h2>
                        </div>
                        <button
                            type="button"
                            @click="checkoutOpen = false"
                            class="size-7 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-xs"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="py-3 flex-1 overflow-y-auto space-y-3.5 pr-1 scrollbar-none">
                        <!-- If Cart Empty -->
                        <div x-show="cart.length === 0" class="py-8 text-center text-xs text-slate-400">
                            <p class="text-3xl mb-2">🛒</p>
                            <p>আপনার কার্ট খালি। মেনু থেকে সুস্বাদু খাবার যোগ করুন!</p>
                        </div>

                        <!-- Cart Items List -->
                        <div x-show="cart.length > 0" class="space-y-2">
                            <template x-for="item in cart" :key="item.id">
                                <div class="p-2.5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="size-11 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-xs text-white truncate" x-text="item.bengali_name"></h4>
                                            <span class="text-[11px] font-black text-[#F85606]" x-text="'৳' + item.price"></span>
                                        </div>
                                    </div>

                                    <!-- Stepper -->
                                    <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-2 py-1">
                                        <button
                                            @click="decreaseQty(item.id)"
                                            type="button"
                                            class="size-5 rounded bg-slate-800 text-white font-black text-xs flex items-center justify-center"
                                        >-</button>
                                        <span class="text-xs font-black text-white px-1" x-text="item.qty"></span>
                                        <button
                                            @click="addToCart(item)"
                                            type="button"
                                            class="size-5 rounded daraz-gradient text-white font-black text-xs flex items-center justify-center"
                                        >+</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Order Type Selection: Dine-in vs Parcel ("কারা পার্সেল নিলো") -->
                        <div x-show="cart.length > 0" class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5">
                            <label class="block text-xs font-bold text-slate-200">খাবার গ্রহণের ধরন নির্বাচন করুন:</label>
                            <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                                <button
                                    type="button"
                                    @click="orderForm.order_type = 'dine_in'"
                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 transition-all"
                                    :class="orderForm.order_type === 'dine_in' ? 'daraz-gradient text-white font-black border-transparent shadow-md' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                >
                                    <span>🪑 বসে খাওয়া (Dine In)</span>
                                </button>
                                <button
                                    type="button"
                                    @click="orderForm.order_type = 'parcel'"
                                    class="py-2.5 rounded-xl border flex items-center justify-center gap-1.5 transition-all"
                                    :class="orderForm.order_type === 'parcel' ? 'bg-blue-600 text-white font-black border-transparent shadow-md' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                >
                                    <span>🛍️ পার্সেল / টেকওয়ে</span>
                                </button>
                            </div>

                            <!-- Table Selection for Dine-in -->
                            <div x-show="orderForm.order_type === 'dine_in'" class="pt-2 border-t border-slate-800/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-amber-400 flex items-center gap-1">
                                        <span>🪑 আপনার টেবিল নম্বর দিন *</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400">খাবার আপনার টেবিলে পৌঁছাবে</span>
                                </div>

                                <div class="grid grid-cols-5 gap-1.5">
                                    <template x-for="t in ['১', '২', '৩', '৪', '৫']">
                                        <button
                                            type="button"
                                            @click="orderForm.table_no = 'টেবিল ' + t"
                                            :class="orderForm.table_no === 'টেবিল ' + t ? 'daraz-gradient text-white font-black border-transparent' : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-amber-500/50'"
                                            class="py-1.5 rounded-xl border text-xs font-bold transition-all text-center"
                                            x-text="'টেবিল ' + t"
                                        ></button>
                                    </template>
                                </div>

                                <input
                                    type="text"
                                    x-model="orderForm.table_no"
                                    placeholder="অথবা টেবিল নম্বর লিখুন (যেমন: টেবিল ৩ বা কাউন্টার সামনে)..."
                                    class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                                />
                            </div>
                        </div>

                        <!-- Customer Details Form -->
                        <div x-show="cart.length > 0" class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5">
                            <h3 class="text-xs font-bold text-slate-200">আপনার কাস্টমার তথ্য:</h3>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">আপনার নাম *</label>
                                    <input
                                        type="text"
                                        x-model="orderForm.customer_name"
                                        placeholder="যেমন: সাকিব"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">মোবাইল নম্বর *</label>
                                    <input
                                        type="tel"
                                        x-model="orderForm.customer_phone"
                                        placeholder="017xxxxxxxx"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">রান্নার বিশেষ অনুরোধ / নোট (ঐচ্ছিক)</label>
                                <input
                                    type="text"
                                    x-model="orderForm.notes"
                                    placeholder="যেমন: ঝাল বেশি, সস আলাদা দিবেন..."
                                    class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                                />
                            </div>
                        </div>

                        <!-- Voucher Input & Apply Section -->
                        <div x-show="cart.length > 0" class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-200 flex items-center gap-1">
                                    <span>🎟️</span>
                                    <span>ডিসকাউন্ট ভাউচার কোড</span>
                                </label>
                                <template x-if="appliedVoucher">
                                    <button type="button" @click="removeVoucher()" class="text-[10px] text-red-400 font-bold hover:underline">রিমুভ করুন</button>
                                </template>
                            </div>

                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    x-model="voucherCode"
                                    placeholder="কুপন লিখুন (যেমন: FOOD50)..."
                                    class="flex-1 px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder:text-slate-600 uppercase font-mono focus:outline-none focus:border-[#F85606]"
                                />
                                <button
                                    type="button"
                                    @click="applyVoucherCode()"
                                    :disabled="isApplyingVoucher"
                                    class="px-3.5 py-2 rounded-xl daraz-gradient text-white text-xs font-black shrink-0 disabled:opacity-50"
                                >
                                    <span x-show="!isApplyingVoucher">প্রয়োগ</span>
                                    <span x-show="isApplyingVoucher">যাচাই...</span>
                                </button>
                            </div>

                            <div x-show="voucherError" class="text-[11px] text-red-400 font-semibold" x-text="voucherError"></div>
                            <div x-show="voucherSuccess" class="text-[11px] text-emerald-400 font-semibold" x-text="voucherSuccess"></div>
                        </div>

                        <!-- Payment Method Selector -->
                        <div x-show="cart.length > 0" class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                            <label class="block text-xs font-bold text-slate-200">পেমেন্ট মাধ্যম নির্বাচন করুন:</label>
                            <div class="grid grid-cols-3 gap-2 text-xs font-bold">
                                <button
                                    type="button"
                                    @click="orderForm.payment_method = 'cash'"
                                    class="py-2.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all"
                                    :class="orderForm.payment_method === 'cash' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 shadow-xs' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                >
                                    <span class="text-sm">💵</span>
                                    <span>ক্যাশ</span>
                                    <span class="text-[9px] opacity-75">কাউন্টারে</span>
                                </button>

                                <button
                                    type="button"
                                    @click="orderForm.payment_method = 'bkash'"
                                    class="py-2.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all"
                                    :class="orderForm.payment_method === 'bkash' ? 'bg-pink-500/20 border-pink-500 text-pink-400 shadow-xs' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                >
                                    <span class="text-sm">📱</span>
                                    <span>বিকাশ</span>
                                    <span class="text-[9px] opacity-75">bKash App</span>
                                </button>

                                <button
                                    type="button"
                                    @click="orderForm.payment_method = 'nagad'"
                                    class="py-2.5 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all"
                                    :class="orderForm.payment_method === 'nagad' ? 'bg-amber-500/20 border-amber-500 text-amber-400 shadow-xs' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white'"
                                >
                                    <span class="text-sm">⚡</span>
                                    <span>নগদ</span>
                                    <span class="text-[9px] opacity-75">Nagad App</span>
                                </button>
                            </div>

                            <!-- bKash Instructions & TrxID Input -->
                            <div x-show="orderForm.payment_method === 'bkash'" class="p-3 rounded-xl bg-pink-500/10 border border-pink-500/30 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="size-5 rounded-md bg-pink-500 text-white font-black text-[10px] flex items-center justify-center">BK</span>
                                        <span class="text-xs font-bold text-pink-400">বিকাশ সেন্ড মানি / মার্চেন্ট:</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyPaymentNumber('{{ $bkashNumber }}', 'বিকাশ')"
                                        class="px-2 py-0.5 rounded-lg bg-pink-500 text-white text-[10px] font-bold hover:brightness-110 flex items-center gap-1"
                                    >
                                        <span>📋 কপি</span>
                                    </button>
                                </div>

                                <div class="p-2 rounded-lg bg-slate-900 border border-pink-500/20 text-center">
                                    <span class="text-sm font-black text-pink-300 font-mono tracking-wider">{{ $bkashNumber }}</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">মোট প্রদেয়: <strong class="text-white" x-text="'৳' + cartTotalPrice()"></strong></span>
                                </div>

                                <div class="text-[10px] text-slate-300 space-y-1 bg-slate-950/60 p-2 rounded-lg">
                                    <p>১. আপনার বিকাশ অ্যাপ থেকে উপরের নম্বরে <strong class="text-pink-400">Send Money</strong> করুন।</p>
                                    <p>২. টাকার পরিমাণ দিন: <strong class="text-white" x-text="'৳' + cartTotalPrice()"></strong></p>
                                    <p>৩. পেমেন্ট শেষে পাওয়া <strong class="text-amber-400">TrxID (ট্রানজেকশন আইডি)</strong> নিচে দিন:</p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-pink-300 mb-1">বিকাশ ট্রানজেকশন আইডি (TrxID) *</label>
                                    <input
                                        type="text"
                                        x-model="orderForm.transaction_id"
                                        placeholder="যেমন: 9J7A2B6C8D"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-pink-500/40 text-white text-xs font-mono uppercase placeholder:text-slate-600 focus:outline-none focus:border-pink-500"
                                    />
                                </div>
                            </div>

                            <!-- Nagad Instructions & TrxID Input -->
                            <div x-show="orderForm.payment_method === 'nagad'" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="size-5 rounded-md bg-amber-500 text-white font-black text-[10px] flex items-center justify-center">NG</span>
                                        <span class="text-xs font-bold text-amber-400">নগদ সেন্ড মানি নম্বর:</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyPaymentNumber('{{ $nagadNumber }}', 'নগদ')"
                                        class="px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] font-bold hover:brightness-110 flex items-center gap-1"
                                    >
                                        <span>📋 কপি</span>
                                    </button>
                                </div>

                                <div class="p-2 rounded-lg bg-slate-900 border border-amber-500/20 text-center">
                                    <span class="text-sm font-black text-amber-300 font-mono tracking-wider">{{ $nagadNumber }}</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">মোট প্রদেয়: <strong class="text-white" x-text="'৳' + cartTotalPrice()"></strong></span>
                                </div>

                                <div class="text-[10px] text-slate-300 space-y-1 bg-slate-950/60 p-2 rounded-lg">
                                    <p>১. নগদ অ্যাপ বা *167# ডায়াল করে <strong class="text-amber-400">Send Money</strong> করুন।</p>
                                    <p>২. টাকার পরিমাণ দিন: <strong class="text-white" x-text="'৳' + cartTotalPrice()"></strong></p>
                                    <p>৩. সফল পেমেন্টের পর পাওয়া <strong class="text-amber-400">TrxID</strong> নিচে লিখুন:</p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-amber-300 mb-1">নগদ ট্রানজেকশন আইডি (TrxID) *</label>
                                    <input
                                        type="text"
                                        x-model="orderForm.transaction_id"
                                        placeholder="যেমন: 8K5B1C4D9F"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-amber-500/40 text-white text-xs font-mono uppercase placeholder:text-slate-600 focus:outline-none focus:border-amber-500"
                                    />
                                </div>
                            </div>

                            <!-- Cash Info Box -->
                            <div x-show="orderForm.payment_method === 'cash'" class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-center gap-2">
                                <span>💵</span>
                                <span>অর্ডার দিলে খাবার রেডি হবে। খাবার গ্রহণের সময় কাউন্টারে ক্যাশ পরিশোধ করবেন।</span>
                            </div>
                        </div>

                        <!-- Error Banner -->
                        <div x-show="errorMessage" class="p-2.5 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-bold" x-text="errorMessage"></div>
                    </div>

                    <!-- Checkout Footer Actions -->
                    <div x-show="cart.length > 0" class="pt-3 border-t border-slate-800 shrink-0 space-y-2">
                        <!-- Bill Summary -->
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>সাবটোটাল:</span>
                                <span class="font-bold text-white" x-text="'৳' + cartSubtotal()"></span>
                            </div>
                            <template x-if="getDiscountAmount() > 0">
                                <div class="flex justify-between text-[#F85606] font-bold">
                                    <span>ভাউচার ডিসকাউন্ট:</span>
                                    <span x-text="'-৳' + getDiscountAmount()"></span>
                                </div>
                            </template>
                            <div class="flex justify-between text-sm font-black text-white pt-1 border-t border-slate-800">
                                <span>সর্বমোট প্রদেয়:</span>
                                <span class="text-base text-[#F85606]" x-text="'৳' + cartTotalPrice()"></span>
                            </div>
                        </div>

                        <!-- Big Daraz Checkout Button -->
                        <button
                            type="button"
                            @click="submitOrder()"
                            :disabled="isSubmitting || !{{ $isCartOpen ? 'true' : 'false' }}"
                            class="w-full py-3.5 rounded-2xl daraz-gradient hover:brightness-110 disabled:opacity-50 text-white font-black text-sm flex items-center justify-between px-4 shadow-xl shadow-[#F85606]/30 active:scale-[0.98] transition-transform"
                        >
                            <span x-show="!isSubmitting">অর্ডার কনফার্ম করুন &rarr;</span>
                            <span x-show="isSubmitting">অপেক্ষা করুন...</span>
                            <span class="text-base font-black" x-text="'৳' + cartTotalPrice()"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 15. MODAL: Order Success Confirmation Modal               -->
            <!-- ========================================================= -->
            <div
                x-show="successOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/85 backdrop-blur-xs flex items-center justify-center p-4"
                x-cloak
            >
                <div
                    @click.outside="successOpen = false"
                    class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 p-5 text-center shadow-2xl space-y-4"
                >
                    <div class="size-16 rounded-full daraz-gradient mx-auto flex items-center justify-center text-3xl shadow-lg shadow-[#F85606]/40 animate-bounce">
                        ✓
                    </div>

                    <div>
                        <h3 class="text-lg font-black text-white">অর্ডার সফলভাবে গ্রহণ করা হয়েছে!</h3>
                        <p class="text-xs text-slate-400 mt-1">রান্নাঘরে আপনার গরম খাবার তৈরি শুরু হয়েছে</p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 space-y-1">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">টোকেন / অর্ডার নম্বর</span>
                        <div class="text-xl font-black text-[#F85606]" x-text="'#' + placedOrderNumber"></div>
                        <span class="text-xs font-bold text-slate-300" x-text="'মোট বিল: ৳' + placedOrderTotal"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button
                            type="button"
                            @click="successOpen = false"
                            class="py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs"
                        >
                            আরও অর্ডার
                        </button>
                        <button
                            type="button"
                            @click="successOpen = false; trackQuery = placedOrderNumber; trackOrder(); trackOpen = true"
                            class="py-2.5 rounded-xl daraz-gradient hover:brightness-110 text-white font-black text-xs shadow-md"
                        >
                            লাইভ ট্র্যাক &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 16. MODAL: Live Order Tracking Modal                      -->
            <!-- ========================================================= -->
            <div
                x-show="trackOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-end justify-center"
                x-cloak
            >
                <div
                    @click.outside="trackOpen = false"
                    class="w-full max-w-md bg-slate-900 border-t border-slate-800 rounded-t-[32px] p-4 max-h-[90vh] flex flex-col shadow-2xl overflow-hidden"
                >
                    <div class="w-12 h-1.5 rounded-full bg-slate-700 mx-auto mb-3 shrink-0"></div>

                    <div class="flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="size-7 rounded-xl daraz-gradient text-white flex items-center justify-center font-black text-sm">🛵</span>
                            <h2 class="text-base font-black text-white">অর্ডার লাইভ ট্র্যাকিং</h2>
                        </div>
                        <button @click="trackOpen = false" class="size-7 rounded-full bg-slate-800 text-slate-400 hover:text-white text-xs flex items-center justify-center">✕</button>
                    </div>

                    <div class="py-3 flex-1 overflow-y-auto space-y-3 pr-1 scrollbar-none">
                        <!-- Search Box -->
                        <div class="flex gap-2">
                            <input
                                type="text"
                                x-model="trackQuery"
                                placeholder="অর্ডার নম্বর বা মোবাইল নম্বর..."
                                class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                            />
                            <button
                                type="button"
                                @click="trackOrder()"
                                class="px-4 py-2.5 rounded-xl daraz-gradient text-white text-xs font-black shrink-0"
                            >
                                ট্র্যাক
                            </button>
                        </div>

                        <div x-show="trackError" class="p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-bold" x-text="trackError"></div>

                        <!-- Found Order Result -->
                        <template x-if="trackResult">
                            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                                <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                    <div>
                                        <span class="text-xs font-black text-white" x-text="'#' + trackResult.order_number"></span>
                                        <span class="text-[10px] text-slate-500 block" x-text="trackResult.created_time"></span>
                                    </div>
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-black"
                                        :class="{
                                            'bg-amber-500/20 text-amber-400 border border-amber-500/30': trackResult.status === 'pending',
                                            'bg-blue-500/20 text-blue-400 border border-blue-500/30': trackResult.status === 'preparing',
                                            'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': trackResult.status === 'ready' || trackResult.status === 'completed'
                                        }"
                                        x-text="trackResult.status_label"
                                    ></span>
                                </div>

                                <div class="space-y-1 text-xs">
                                    <div class="text-slate-400">কাস্টমার: <span class="text-white font-bold" x-text="trackResult.customer_name"></span></div>
                                    <div class="text-slate-400">মোট বিল: <span class="text-[#F85606] font-black" x-text="'৳' + trackResult.total_amount"></span></div>
                                </div>

                                <!-- Items -->
                                <div class="pt-2 border-t border-slate-800 space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold block">অর্ডারের খাবার:</span>
                                    <template x-for="itm in trackResult.items" :key="itm.id">
                                        <div class="flex justify-between text-xs text-slate-300">
                                            <span x-text="itm.food_name + ' × ' + itm.quantity"></span>
                                            <span class="font-bold" x-text="'৳' + itm.subtotal"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 17. MODAL: Table QR Code Modal                            -->
            <!-- ========================================================= -->
            <div
                x-show="qrModalOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
                x-cloak
            >
                <div
                    @click.outside="qrModalOpen = false"
                    class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 p-5 text-center shadow-2xl space-y-3"
                >
                    <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white flex items-center gap-1.5">
                            <span>📱</span>
                            <span>ডিজিটাল কিউআর মেনু</span>
                        </h3>
                        <button @click="qrModalOpen = false" class="size-6 rounded-full bg-slate-800 text-slate-400 hover:text-white text-xs">✕</button>
                    </div>

                    <p class="text-xs text-slate-400">কাস্টমাররা ক্যামেরা দিয়ে স্ক্যান করলেই এই মেনু সরাসরি ওপেন হবে</p>

                    <div class="bg-white p-4 rounded-2xl inline-block shadow-inner">
                        <img
                            :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(currentUrl)"
                            alt="Table QR Code"
                            class="size-44 mx-auto"
                        />
                    </div>

                    <div class="text-xs text-slate-300 font-bold">
                        {{ $cartName }}<br>
                        <span class="text-slate-500 font-normal text-[10px]">{{ $cartAddress }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button
                            type="button"
                            @click="window.print()"
                            class="py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs"
                        >
                            🖨️ প্রিন্ট
                        </button>
                        <button
                            type="button"
                            @click="navigator.clipboard.writeText(currentUrl); alert('লিংক কপি করা হয়েছে!')"
                            class="py-2.5 rounded-xl daraz-gradient text-white font-bold text-xs"
                        >
                            🔗 লিংক কপি
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 18. MODAL: Customer Review & Rating Modal                 -->
            <!-- ========================================================= -->
            <div
                x-show="reviewModalOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
                x-cloak
            >
                <div
                    @click.outside="reviewModalOpen = false"
                    class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 p-5 text-start shadow-2xl space-y-3"
                >
                    <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base">⭐</span>
                            <h3 class="text-sm font-black text-white">মতামত ও রিভিউ দিন</h3>
                        </div>
                        <button @click="reviewModalOpen = false" class="size-6 rounded-full bg-slate-800 text-slate-400 hover:text-white text-xs flex items-center justify-center">✕</button>
                    </div>

                    <!-- Star Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1">রেটিং নির্বাচন করুন:</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button
                                    type="button"
                                    @click="reviewForm.rating = star"
                                    class="text-2xl transition-transform hover:scale-125 focus:outline-none"
                                    :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-slate-700'"
                                >
                                    ★
                                </button>
                            </template>
                            <span class="text-xs font-black text-amber-400 ml-1" x-text="reviewForm.rating + ' / ৫'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">আপনার নাম *</label>
                        <input
                            type="text"
                            x-model="reviewForm.customer_name"
                            placeholder="যেমন: সাকিব হাসান"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">মোবাইল নম্বর (ঐচ্ছিক)</label>
                        <input
                            type="tel"
                            x-model="reviewForm.customer_phone"
                            placeholder="017xxxxxxxx"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">আপনার রিভিউ বা মন্তব্য *</label>
                        <textarea
                            x-model="reviewForm.comment"
                            rows="3"
                            placeholder="খাবারের স্বাদ, প্যাকেজিং ও সার্ভিস কেমন লেগেছে লিখুন..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder:text-slate-600 focus:outline-none focus:border-[#F85606]"
                        ></textarea>
                    </div>

                    <div x-show="reviewErrorMsg" class="text-[11px] text-red-400 font-semibold" x-text="reviewErrorMsg"></div>
                    <div x-show="reviewSuccessMsg" class="text-[11px] text-emerald-400 font-semibold" x-text="reviewSuccessMsg"></div>

                    <div class="pt-2 flex gap-2">
                        <button
                            type="button"
                            @click="reviewModalOpen = false"
                            class="flex-1 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs"
                        >
                            বাতিল
                        </button>
                        <button
                            type="button"
                            @click="submitReview()"
                            :disabled="isSubmittingReview"
                            class="flex-1 py-2.5 rounded-xl daraz-gradient hover:brightness-110 text-white font-bold text-xs shadow-md disabled:opacity-50"
                        >
                            <span x-show="!isSubmittingReview">রিভিউ সাবমিট</span>
                            <span x-show="isSubmittingReview">অপেক্ষা করুন...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- 19. MODAL: Staff & Owner Secure Login Modal               -->
            <!-- ========================================================= -->
            <div
                x-show="loginModalOpen"
                x-transition
                class="fixed inset-0 z-50 bg-black/85 backdrop-blur-xs flex items-center justify-center p-4"
                x-cloak
            >
                <div
                    @click.outside="loginModalOpen = false"
                    class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 p-5 text-start shadow-2xl space-y-4"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="size-8 rounded-xl daraz-gradient flex items-center justify-center text-white text-base font-bold shadow-md shadow-[#F85606]/30">🧑‍🍳</span>
                            <div>
                                <h3 class="text-sm font-black text-white">স্টাফ ও ওনার লগইন প্যানেল</h3>
                                <p class="text-[10px] text-slate-400">ফুডকার্ট পরিচালনা ও অর্ডার নিয়ন্ত্রণ</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="loginModalOpen = false"
                            class="size-7 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-xs"
                        >✕</button>
                    </div>

                    @auth
                        <!-- If already logged in -->
                        <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">✅</span>
                                <div>
                                    <h4 class="text-xs font-bold text-white">{{ auth()->user()->name }}</h4>
                                    <p class="text-[10px] text-emerald-400 font-semibold">
                                        আপনি বর্তমানে {{ auth()->user()->isOwner() ? '👑 ওনার (Owner)' : '🧑‍🍳 কার্টবয় (Staff)' }} হিসেবে লগইন আছেন।
                                    </p>
                                </div>
                            </div>
                            <div class="pt-1 flex gap-2">
                                <a
                                    href="{{ auth()->user()->isOwner() ? route('dashboard') : route('cartboy.index') }}"
                                    class="flex-1 py-2.5 rounded-xl daraz-gradient text-white text-xs font-black text-center shadow-xs"
                                >
                                    {{ auth()->user()->isOwner() ? 'ওনার ড্যাশবোর্ডে প্রবেশ &rarr;' : 'কাউন্টার প্যানেলে প্রবেশ &rarr;' }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-red-500/20 text-slate-300 hover:text-red-400 text-xs font-bold border border-slate-700">
                                        লগআউট
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Direct Email / Phone & Password Login Form -->
                        <form method="POST" action="{{ route('login.store') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">ইমেইল বা মোবাইল নম্বর *</label>
                                <input
                                    type="text"
                                    name="email"
                                    required
                                    autofocus
                                    placeholder="017xxxxxxxx বা user@example.com"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder:text-slate-500 outline-none focus:border-[#F85606]"
                                />
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">পাসওয়ার্ড *</label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    placeholder="••••••••"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder:text-slate-500 outline-none focus:border-[#F85606]"
                                />
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="remember" class="rounded text-[#F85606] focus:ring-0 bg-slate-950 border-slate-700" />
                                    <span>লগইন মনে রাখুন</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="w-full py-2.5 rounded-xl daraz-gradient hover:brightness-110 text-white font-black text-xs shadow-md flex items-center justify-center gap-1.5"
                            >
                                <span>🔒</span>
                                <span>প্যানেলে লগইন করুন</span>
                            </button>
                        </form>

                        <!-- Quick 1-Click Demo Buttons -->
                        <div class="pt-2 border-t border-slate-800 space-y-2">
                            <span class="block text-[10px] text-slate-400 font-semibold text-center">অথবা দ্রুত প্রবেশ করুন (১-ক্লিক ডেমো লগইন):</span>
                            <div class="grid grid-cols-2 gap-2">
                                <a
                                    href="{{ route('demo.login', 'owner') }}"
                                    class="p-2 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300 hover:bg-amber-500/25 text-center text-xs font-bold transition-all"
                                >
                                    👑 ওনার (Owner)
                                </a>
                                <a
                                    href="{{ route('demo.login', 'staff') }}"
                                    class="p-2 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-300 hover:bg-blue-500/25 text-center text-xs font-bold transition-all"
                                >
                                    🧑‍🍳 কার্টবয় (Staff)
                                </a>
                            </div>
                        </div>

                        <!-- Full page link -->
                        <div class="text-center pt-1">
                            <a href="{{ route('login') }}" class="text-[11px] text-slate-400 hover:text-white underline">
                                সম্পূর্ণ লগইন পেজে যেতে চান? এখানে ক্লিক করুন &rarr;
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

        </div>
    </div>

</body>
</html>
