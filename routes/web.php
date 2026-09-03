<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CartBoyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\DailyClosingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WasteController;
use Illuminate\Support\Facades\Route;

// Public Customer Digital Menu & Ordering Routes
Route::get('/', [CustomerMenuController::class, 'index'])->name('home');
Route::post('/customer-order', [CustomerMenuController::class, 'storeOrder'])->name('customer.order');
Route::get('/track-order', [CustomerMenuController::class, 'track'])->name('customer.track');
Route::post('/customer-review', [CustomerMenuController::class, 'storeReview'])->name('customer.review');
Route::post('/apply-coupon', [CustomerMenuController::class, 'applyCoupon'])->name('customer.coupon');

// 1-Click Demo Login Routes
Route::get('/demo-login/{role}', [DemoLoginController::class, 'loginAs'])->name('demo.login');

// Theme Switcher Route
Route::post('/switch-theme', [SettingController::class, 'switchTheme'])->name('theme.switch');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dedicated Cart Boy / Staff Counter & Kitchen Section
    Route::get('/cartboy', [CartBoyController::class, 'index'])->name('cartboy.index');
    Route::get('/cartboy/live-orders', [CartBoyController::class, 'liveOrdersJson'])->name('cartboy.live-orders');
    Route::patch('/cartboy/orders/{order}/status', [CartBoyController::class, 'updateOrderStatus'])->name('cartboy.order-status');

    // Dashboard (Owner Only - non-owner redirected to /cartboy)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS & Orders (Staff & Owner)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');
    Route::post('/pos/validate-coupon', [PosController::class, 'validateCoupon'])->name('pos.validate-coupon');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    // Menu: Foods & Categories (Viewing is available for all staff; modifying can be owner/manager)
    Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
    Route::post('/foods/{food}/toggle-active', [FoodController::class, 'toggleActive'])->name('foods.toggle-active');

    // Waste Management (Staff & Owner can log waste)
    Route::get('/wastes', [WasteController::class, 'index'])->name('wastes.index');
    Route::post('/wastes', [WasteController::class, 'store'])->name('wastes.store');
    Route::delete('/wastes/{waste}', [WasteController::class, 'destroy'])->name('wastes.destroy');

    // Inventory & Stock (Staff & Owner can view stock)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{food}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Customers (Staff can view and register customers at POS)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Smart Insights (All staff can see insights to improve operations)
    Route::get('/insights', [InsightsController::class, 'index'])->name('insights.index');

    // Quick Price Customization & Discount Toggle (Staff & Owner)
    Route::patch('/foods/{food}/price', [FoodController::class, 'updatePrice'])->name('foods.update-price');

    // Daily Business Closing & Cart Open/Close (Staff & Owner)
    Route::get('/daily-closing', [DailyClosingController::class, 'index'])->name('closing.index');
    Route::post('/daily-closing/close', [DailyClosingController::class, 'close'])->name('closing.close');
    Route::post('/cart/toggle-status', [DailyClosingController::class, 'toggleCartStatus'])->name('cart.toggle-status');

    // =========================================================================
    // OWNER-RESTRICTED FINANCIAL & MANAGEMENT ROUTES
    // =========================================================================
    Route::middleware(['owner'])->group(function () {
        // Food Management (Create, Edit, Delete)
        Route::get('/foods/create', [FoodController::class, 'create'])->name('foods.create');
        Route::post('/foods', [FoodController::class, 'store'])->name('foods.store');
        Route::get('/foods/{food}/edit', [FoodController::class, 'edit'])->name('foods.edit');
        Route::put('/foods/{food}', [FoodController::class, 'update'])->name('foods.update');
        Route::delete('/foods/{food}', [FoodController::class, 'destroy'])->name('foods.destroy');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Payments Ledger
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

        // Purchases & Suppliers
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Profit & Loss
        Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss.index');

        // Daily Business Reopening (Strictly Owner Only)
        Route::post('/daily-closing/{dailyReport}/reopen', [DailyClosingController::class, 'reopen'])->name('closing.reopen');

        // Loyalty Rules & Coupons
        Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
        Route::post('/loyalty/ratio', [LoyaltyController::class, 'updateRatio'])->name('loyalty.update-ratio');

        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggleActive'])->name('coupons.toggle');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        // Employees & Attendance
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('/reports/waste', [ReportController::class, 'waste'])->name('reports.waste');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/export/{type}', [ReportController::class, 'exportCsv'])->name('reports.export');

        // Cart Settings
        Route::get('/settings/cart', [SettingController::class, 'index'])->name('settings.cart');
        Route::post('/settings/cart', [SettingController::class, 'update'])->name('settings.cart.update');
    });
});

require __DIR__.'/settings.php';
