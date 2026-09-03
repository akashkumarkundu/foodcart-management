# 🍔 Food Cart Management System (রেশম নগরী বাইটস)

A modern, high-performance, real-time **Food Cart & Restaurant Management POS Web Application** built with Laravel 12, Livewire, Alpine.js, and Tailwind CSS. Tailored specifically for street food carts, cafes, and restaurants.

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/akashkumarkundu/foodcart-management)
&nbsp;
[![Deploy on Railway](https://railway.com/button.svg)](https://railway.com/new/template?template=https://github.com/akashkumarkundu/foodcart-management)

---

## ✨ Features & Capabilities

### 📱 1. Live Interactive Customer Menu
- **Hero Brand Header**: Displays official food cart branding (*রেশম নগরী বাইটস / Resham Nogori Bites*), innovative glowing animated vector logo, and a **real-time live ticking digital clock**.
- **Dynamic Flash Deals & Vouchers**: Countdown timer with instant voucher collection.
- **Smart Order Flow**: Supports both **Dine-in (টেবিল নং ১-৫)** and **Takeaway / Parcel (পার্সেল)**.
- **Digital Payments**: Integrated bKash and Nagad payment options with copyable phone numbers and Transaction ID (TrxID) input.
- **Customer Ratings & Reviews**: Real customer feedback with star ratings.
- **Theme Switcher**: Instant toggle between ☀️ Light Mode and 🌙 Dark Mode with auto-persisted local storage.
- **Order Tracking**: Real-time status lookup by Order Number with animated progress bars and sound alerts.

### 🧑‍🍳 2. Staff Counter & CartBoy Section
- **Live Orders Feed**: Real-time order pipeline (Pending, Cooking, Ready, Completed).
- **Audio & Visual Alerts**: Instant sound alert and visual badges when orders are ready.
- **Dine-in vs Parcel Tracking**: Clear indicators so the counter knows whether to plate or pack the food.
- **Quick Status Updates**: One-tap progression from Cooking to Delivered.

### 🛒 3. Responsive Food Cart POS Terminal
- **Mobile-First 2-Column Catalog**: Spacious, readable food cards with Bengali names, stock badges, and pricing.
- **Mobile Tab Switcher**: Smooth tab navigation between `[ 🍽️ খাবার মেনু ]` and `[ 🛒 অর্ডার কার্ট ]`.
- **Floating Bottom Cart Pill**: Displays item count and total bill with instant checkout drawer trigger.
- **Real-Time Calculation**: Automatic subtotal, discount vouchers, and grand total.

### 👑 4. Owner Dashboard & Management
- **Item-wise Real-Time Sales Breakdown**: Quantity and revenue for each food item.
- **Payment Method Breakdown**: Cash in hand vs digital payments (bKash, Nagad, Card).
- **Daily Closing Reports**: End-of-day register closing and cash reconciliation.
- **Inventory & Purchases**: Real-time ingredient tracking and low-stock alerts.
- **Staff Attendance & Payroll**: Employee check-in and salary management.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Blade, Livewire, Alpine.js, Tailwind CSS, Vite
- **Testing**: Pest PHP (72 automated tests, 233 assertions passing)
- **Code Quality**: Laravel Pint
- **Database**: SQLite / MySQL / PostgreSQL

---

## 🚀 Installation & Local Setup

```bash
# 1. Clone repository
git clone https://github.com/akashkumarkundu/foodcart-management.git
cd foodcart-management

# 2. Install dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run database migrations & seeders
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Start development server
php artisan serve
```

---

## 🧪 Automated Testing

```bash
php artisan test --compact
```
All **72 unit & feature tests** run and pass with **0 failures**.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
