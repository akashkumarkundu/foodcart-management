<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Food;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Waste;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FoodCartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Core App Settings
        $settings = [
            'cart_name' => 'রেশম নগরী বাইটস (Resham Nogori Bites)',
            'cart_phone' => '01712-345678',
            'cart_address' => 'টি-বাঁধ সংলগ্ন, পদ্মা গার্ডেন রোড, রাজশাহী',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'tax_percentage' => '0',
            'receipt_footer' => 'ধন্যবাদ! রেশম নগরী বাইটসে আবার আসবেন। (Visit Us Again!)',
            'loyalty_points_ratio' => '100',
            'active_theme' => 'bangladesh',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 2. Seed Users: 1 Owner & 1 Staff
        $owner = User::firstOrCreate(
            ['email' => 'owner@foodcart.test'],
            [
                'name' => 'Tanvir Chowdhury (Owner)',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '01711000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@foodcart.test'],
            [
                'name' => 'Rahim Uddin (Staff)',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '01811000002',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Seed 9 Authentic Bangladeshi Food Cart Categories
        $categoriesData = [
            ['name' => 'Fast Food & Burgers', 'bengali_name' => 'বার্গার ও ফাস্টফুড', 'slug' => 'fast-food', 'sort_order' => 1],
            ['name' => 'Tea & Coffee', 'bengali_name' => 'চা ও কফি স্পেশাল', 'slug' => 'tea-coffee', 'sort_order' => 2],
            ['name' => 'Noodles & Chowmein', 'bengali_name' => 'নুডুলস ও চাওমিন', 'slug' => 'noodles', 'sort_order' => 3],
            ['name' => 'Pasta Specials', 'bengali_name' => 'পাস্তা স্পেশাল', 'slug' => 'pasta', 'sort_order' => 4],
            ['name' => 'Chicken Tehari', 'bengali_name' => 'চিকেন তেহারি', 'slug' => 'chicken-tehari', 'sort_order' => 5],
            ['name' => 'Shahi Halim', 'bengali_name' => 'শাহী হালিম', 'slug' => 'halim', 'sort_order' => 6],
            ['name' => 'Chotpoti Special', 'bengali_name' => 'চটপটি স্পেশাল', 'slug' => 'chotpoti', 'sort_order' => 7],
            ['name' => 'Crispy Fuchka', 'bengali_name' => 'ফুচকা স্পেশাল', 'slug' => 'fuska', 'sort_order' => 8],
            ['name' => 'Kebab & Rolls', 'bengali_name' => 'কাবাব ও রোল', 'slug' => 'kebab-items', 'sort_order' => 9],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::create($c);
        }

        // 4. Seed Authentic Bangladeshi Food Cart Items (Fast Food, Tea/Coffee, Noodles, Pasta, Tehari, Halim, Chotpoti, Fuska, Kebab)
        $foodsData = [
            // 1. Fast Food & Burgers
            ['cat' => 'fast-food', 'name' => 'Dhakaiya Beef Naga Burger', 'bn' => 'ঝাল বিফ নাগা বার্গার', 'sp' => 190, 'cp' => 105, 'stock' => 50, 'unit' => 'pcs', 'prep' => 10],
            ['cat' => 'fast-food', 'name' => 'Crispy Chicken Cheese Burger', 'bn' => 'চিকেন চিজ বার্গার', 'sp' => 175, 'cp' => 90, 'stock' => 45, 'unit' => 'pcs', 'prep' => 8],
            ['cat' => 'fast-food', 'name' => 'Chicken Shawarma Wrap', 'bn' => 'চিকেন শর্মা র‍্যাপ', 'sp' => 130, 'cp' => 65, 'stock' => 40, 'unit' => 'pcs', 'prep' => 5],
            ['cat' => 'fast-food', 'name' => 'Loaded French Fries with Mayo', 'bn' => 'স্পাইসি ফ্রেঞ্চ ফ্রাইজ', 'sp' => 110, 'cp' => 45, 'stock' => 55, 'unit' => 'box', 'prep' => 7],
            ['cat' => 'fast-food', 'name' => 'Crispy Chicken Wings (6 pcs)', 'bn' => 'স্পাইসি চিকেন উইংস', 'sp' => 190, 'cp' => 95, 'stock' => 35, 'unit' => 'box', 'prep' => 12],

            // 2. Varieties Tea & Coffee
            ['cat' => 'tea-coffee', 'name' => 'Dhakaiya Malai Cha', 'bn' => 'ঢাকাইয়া মালাই চা', 'sp' => 35, 'cp' => 14, 'stock' => 120, 'unit' => 'cup', 'prep' => 3],
            ['cat' => 'tea-coffee', 'name' => 'Ginger Milk Tea (আদা দুধ চা)', 'bn' => 'আদা দুধ চা', 'sp' => 25, 'cp' => 9, 'stock' => 120, 'unit' => 'cup', 'prep' => 3],
            ['cat' => 'tea-coffee', 'name' => 'Masala Dudh Cha (শাহী মসলা দুধ চা)', 'bn' => 'শাহী মসলা দুধ চা', 'sp' => 30, 'cp' => 11, 'stock' => 100, 'unit' => 'cup', 'prep' => 3],
            ['cat' => 'tea-coffee', 'name' => 'Special Lemon Green Tea', 'bn' => 'লেবু রং চা', 'sp' => 15, 'cp' => 5, 'stock' => 150, 'unit' => 'cup', 'prep' => 2],
            ['cat' => 'tea-coffee', 'name' => 'Hot Espresso Coffee', 'bn' => 'হট কফি স্পেশাল', 'sp' => 60, 'cp' => 22, 'stock' => 80, 'unit' => 'cup', 'prep' => 4],
            ['cat' => 'tea-coffee', 'name' => 'Cold Coffee with Ice Cream', 'bn' => 'কোল্ড কফি উইথ আইসক্রিম', 'sp' => 90, 'cp' => 38, 'stock' => 60, 'unit' => 'glass', 'prep' => 5],

            // 3. Varieties Noodles & Chowmein
            ['cat' => 'noodles', 'name' => 'Street Style Egg Chowmein', 'bn' => 'ডিম চাউমিন স্পেশাল', 'sp' => 90, 'cp' => 40, 'stock' => 45, 'unit' => 'plate', 'prep' => 6],
            ['cat' => 'noodles', 'name' => 'Chicken Chowmein Special', 'bn' => 'চিকেন চাউমিন স্পেশাল', 'sp' => 150, 'cp' => 75, 'stock' => 40, 'unit' => 'plate', 'prep' => 8],
            ['cat' => 'noodles', 'name' => 'Spicy Naga Chicken Noodles', 'bn' => 'স্পাইসি নাগা চিকেন নুডুলস', 'sp' => 170, 'cp' => 85, 'stock' => 35, 'unit' => 'plate', 'prep' => 8],
            ['cat' => 'noodles', 'name' => 'Mix Special Chowmein (Chicken, Beef & Egg)', 'bn' => 'মিক্স স্পেশাল চাউমিন', 'sp' => 210, 'cp' => 110, 'stock' => 30, 'unit' => 'plate', 'prep' => 10],

            // 4. Pasta Specials
            ['cat' => 'pasta', 'name' => 'Oven Baked Chicken Pasta', 'bn' => 'ওভেন বেকড চিকেন পাস্তা', 'sp' => 220, 'cp' => 110, 'stock' => 35, 'unit' => 'bowl', 'prep' => 12],
            ['cat' => 'pasta', 'name' => 'Creamy White Sauce Pasta', 'bn' => 'হোয়াইট সস চিজ পাস্তা', 'sp' => 190, 'cp' => 95, 'stock' => 35, 'unit' => 'bowl', 'prep' => 10],
            ['cat' => 'pasta', 'name' => 'Spicy Naga Chicken Pasta', 'bn' => 'ঝাল নাগা চিকেন পাস্তা', 'sp' => 200, 'cp' => 100, 'stock' => 30, 'unit' => 'bowl', 'prep' => 10],

            // 5. Chicken Tehari
            ['cat' => 'chicken-tehari', 'name' => 'Puran Dhaka Chicken Tehari (Half)', 'bn' => 'পুরান ঢাকার চিকেন তেহারি (হাফ)', 'sp' => 140, 'cp' => 75, 'stock' => 45, 'unit' => 'plate', 'prep' => 3],
            ['cat' => 'chicken-tehari', 'name' => 'Puran Dhaka Chicken Tehari (Full)', 'bn' => 'পুরান ঢাকার চিকেন তেহারি (ফুল)', 'sp' => 230, 'cp' => 125, 'stock' => 35, 'unit' => 'plate', 'prep' => 3],
            ['cat' => 'chicken-tehari', 'name' => 'Chicken Tehari with Egg & Salad', 'bn' => 'ডিম চিকেন তেহারি স্পেশাল', 'sp' => 260, 'cp' => 140, 'stock' => 30, 'unit' => 'plate', 'prep' => 4],

            // 6. Shahi Halim
            ['cat' => 'halim', 'name' => 'Shahi Beef Halim (Bowl)', 'bn' => 'শাহী বিফ হালিম বাটি', 'sp' => 160, 'cp' => 80, 'stock' => 40, 'unit' => 'bowl', 'prep' => 3],
            ['cat' => 'halim', 'name' => 'Special Royal Mutton Halim (Bowl)', 'bn' => 'শাহী খাসির হালিম বাটি', 'sp' => 220, 'cp' => 115, 'stock' => 30, 'unit' => 'bowl', 'prep' => 3],
            ['cat' => 'halim', 'name' => 'Shahi Chicken Halim (Bowl)', 'bn' => 'শাহী চিকেন হালিম বাটি', 'sp' => 140, 'cp' => 70, 'stock' => 35, 'unit' => 'bowl', 'prep' => 3],

            // 7. Chotpoti
            ['cat' => 'chotpoti', 'name' => 'Egg Chotpoti with Tamarind Sauce', 'bn' => 'ডিম চটপটি তেঁতুল টক', 'sp' => 70, 'cp' => 25, 'stock' => 50, 'unit' => 'plate', 'prep' => 4],
            ['cat' => 'chotpoti', 'name' => 'Special Tok-Mishti Doi Chotpoti', 'bn' => 'টক-মিষ্টি দই চটপটি', 'sp' => 100, 'cp' => 40, 'stock' => 40, 'unit' => 'plate', 'prep' => 4],
            ['cat' => 'chotpoti', 'name' => 'Spicy Naga Jhal Chotpoti', 'bn' => 'ঝাল নাগা চটপটি', 'sp' => 85, 'cp' => 32, 'stock' => 40, 'unit' => 'plate', 'prep' => 4],

            // 8. Fuska (Fuchka)
            ['cat' => 'fuska', 'name' => 'Crispy Special Fuchka (10 pcs)', 'bn' => 'মচমচে স্পেশাল ফুচকা (১০ পিস)', 'sp' => 80, 'cp' => 30, 'stock' => 60, 'unit' => 'plate', 'prep' => 4],
            ['cat' => 'fuska', 'name' => 'Doi Fuchka with Sweet Yogurt', 'bn' => 'টক মিষ্টি দই ফুচকা', 'sp' => 120, 'cp' => 48, 'stock' => 45, 'unit' => 'plate', 'prep' => 4],
            ['cat' => 'fuska', 'name' => 'Naga Jhal Fuchka (Extra Spicy)', 'bn' => 'স্পাইসি নাগা ফুচকা', 'sp' => 100, 'cp' => 40, 'stock' => 50, 'unit' => 'plate', 'prep' => 4],

            // 9. Kebab & Rolls
            ['cat' => 'kebab-items', 'name' => 'Chicken Shik Kebab', 'bn' => 'চিকেন শিক কাবাব', 'sp' => 130, 'cp' => 65, 'stock' => 45, 'unit' => 'stick', 'prep' => 10],
            ['cat' => 'kebab-items', 'name' => 'Chicken Reshmi Kebab', 'bn' => 'রেশমি কাবাব', 'sp' => 160, 'cp' => 80, 'stock' => 30, 'unit' => 'stick', 'prep' => 10],
            ['cat' => 'kebab-items', 'name' => 'Beef Jali Kebab (2 pcs)', 'bn' => 'বিফ জালি কাবাব (২ পিস)', 'sp' => 90, 'cp' => 45, 'stock' => 40, 'unit' => 'plate', 'prep' => 5],
            ['cat' => 'kebab-items', 'name' => 'Chicken Kebab Paratha Roll', 'bn' => 'চিকেন কাবাব পরোটা রোল', 'sp' => 120, 'cp' => 55, 'stock' => 50, 'unit' => 'pcs', 'prep' => 6],
        ];

        $foods = [];
        foreach ($foodsData as $f) {
            $created = Food::create([
                'category_id' => $categories[$f['cat']]->id,
                'name' => $f['name'],
                'bengali_name' => $f['bn'],
                'slug' => Str::slug($f['name']),
                'selling_price' => $f['sp'],
                'cost_price' => $f['cp'],
                'preparation_time' => $f['prep'],
                'current_stock' => $f['stock'],
                'min_stock' => 10,
                'unit' => $f['unit'],
                'is_active' => true,
            ]);
            $foods[] = $created;
        }

        // 5. Seed Suppliers (Wholesale Vendors in Dhaka)
        $suppliersData = [
            ['name' => 'Karwan Bazar Poultry & Meat', 'contact_person' => 'Mohammad Rafiq', 'phone' => '01711223344', 'products_supplied' => 'Broiler, Deshi chicken, beef cuts', 'balance_due' => 0],
            ['name' => 'Chawkbazar Spice & Oil Wholesale', 'contact_person' => 'Haji Abdur Rahman', 'phone' => '01819334455', 'products_supplied' => 'Mustard oil, chili, turmeric, biryani masala', 'balance_due' => 1500],
            ['name' => 'Bengal Bakery & Bun Supply', 'contact_person' => 'Zakir Hossain', 'phone' => '01912445566', 'products_supplied' => 'Burger buns, shawarma wraps, paratha dough', 'balance_due' => 0],
            ['name' => 'Dhaka Eco Pack & Disposables', 'contact_person' => 'Kamal Uddin', 'phone' => '01615556677', 'products_supplied' => 'Paper plates, foil boxes, cups, straws', 'balance_due' => 800],
        ];

        $suppliers = [];
        foreach ($suppliersData as $s) {
            $suppliers[] = Supplier::create($s);
        }

        // 6. Seed Expense Categories & Expenses
        $expenseCategoriesData = [
            ['name' => 'Gas & Cylinder Fuel', 'slug' => 'gas-cylinder', 'description' => '12kg and 35kg LP gas refills'],
            ['name' => 'Packaging & Disposable Boxes', 'slug' => 'packaging', 'description' => 'Boxes, spoons, paper bags, foil wrap'],
            ['name' => 'Stall Rent & Electricity', 'slug' => 'rent-electricity', 'description' => 'Daily cart spot fee and generator power'],
            ['name' => 'Staff Daily Allowance & Refreshment', 'slug' => 'staff-allowance', 'description' => 'Meals and tea for staff during shift'],
            ['name' => 'Maintenance & Cleaning Supplies', 'slug' => 'maintenance-cleaning', 'description' => 'Soap, sanitizer, towel, stove repair'],
        ];

        $expenseCategories = [];
        foreach ($expenseCategoriesData as $ec) {
            $expenseCategories[] = ExpenseCategory::create($ec);
        }

        // 7. Seed Employees
        $employeesData = [
            ['name' => 'Md. Shakil Ahmed', 'phone' => '01722334455', 'position' => 'Chef', 'salary' => 18000, 'joining_date' => now()->subMonths(6)],
            ['name' => 'Sabbir Hossain', 'phone' => '01833445566', 'position' => 'Cashier', 'salary' => 14000, 'joining_date' => now()->subMonths(4)],
            ['name' => 'Sohel Rana', 'phone' => '01944556677', 'position' => 'Helper', 'salary' => 10000, 'joining_date' => now()->subMonths(2)],
        ];

        $employees = [];
        foreach ($employeesData as $e) {
            $employees[] = Employee::create($e);
        }

        // Seed 7 days of attendance for employees
        for ($i = 0; $i < 7; $i++) {
            $attDate = Carbon::today()->subDays($i);
            foreach ($employees as $emp) {
                Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => $attDate,
                    'status' => ($i === 3 && $emp->id === $employees[2]->id) ? 'leave' : 'present',
                    'notes' => ($i === 3 && $emp->id === $employees[2]->id) ? 'Sick leave' : null,
                ]);
            }
        }

        // 8. Seed Discount Coupons
        Coupon::create([
            'code' => 'FOOD50',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'min_order_amount' => 400,
            'max_discount_amount' => 50,
            'description' => '৳50 off on minimum order of ৳400',
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'BURGER20',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'min_order_amount' => 300,
            'max_discount_amount' => 75,
            'description' => '15% off up to ৳75 on orders above ৳300',
            'is_active' => true,
        ]);

        // 9. Seed Customers (Realistic Bangladeshi Profiles)
        $customersData = [
            ['name' => 'Tanveer Rahman', 'phone' => '01715123456', 'email' => 'tanveer.bd@gmail.com', 'points' => 35],
            ['name' => 'Farhana Sultana', 'phone' => '01823987654', 'email' => 'farhana.s@yahoo.com', 'points' => 62],
            ['name' => 'Ashfaqul Islam', 'phone' => '01912456789', 'email' => 'ashfaq.cse@gmail.com', 'points' => 20],
            ['name' => 'Nusrat Jahan', 'phone' => '01678112233', 'email' => 'nusrat.j@gmail.com', 'points' => 48],
            ['name' => 'Kazi Mamun', 'phone' => '01755667788', 'email' => 'kazi.mamun@gmail.com', 'points' => 85],
            ['name' => 'Shakib Al Hasan Jr', 'phone' => '01511223344', 'email' => 'shakib.jr@gmail.com', 'points' => 15],
        ];

        $customers = [];
        foreach ($customersData as $c) {
            $cust = Customer::create([
                'name' => $c['name'],
                'phone' => $c['phone'],
                'email' => $c['email'],
                'total_orders' => 0,
                'total_spent' => 0,
                'loyalty_points' => $c['points'],
            ]);
            $customers[] = $cust;
        }

        // 10. Seed Realistic Historical Orders Across Past 30 Days
        $paymentMethods = ['cash', 'cash', 'bkash', 'bkash', 'nagad', 'rocket', 'card'];
        $orderCounter = 1;

        for ($day = 30; $day >= 0; $day--) {
            $date = Carbon::today()->subDays($day);
            // Between 5 to 14 orders per day
            $ordersTodayCount = ($day === 0) ? 8 : rand(6, 12);

            for ($o = 0; $o < $ordersTodayCount; $o++) {
                // Realistic hour distribution: rush between 1 PM - 3 PM and 6 PM - 10 PM
                $hour = (rand(0, 1) === 0) ? rand(13, 15) : rand(18, 22);
                $minute = rand(0, 59);
                $createdAt = $date->copy()->setTime($hour, $minute);

                $customer = (rand(0, 10) > 3) ? $customers[array_rand($customers)] : null;
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                // Pick 1 to 3 food items
                $numItems = rand(1, 3);
                $selectedFoods = (array) array_rand($foods, $numItems);

                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($selectedFoods as $idx) {
                    $foodItem = $foods[$idx];
                    $qty = rand(1, 3);
                    $lineTotal = $foodItem->selling_price * $qty;
                    $lineProfit = ($foodItem->selling_price - $foodItem->cost_price) * $qty;
                    $subtotal += $lineTotal;

                    $itemsToCreate[] = [
                        'food_id' => $foodItem->id,
                        'food_name' => $foodItem->name,
                        'unit_price' => $foodItem->selling_price,
                        'cost_price' => $foodItem->cost_price,
                        'quantity' => $qty,
                        'subtotal' => $lineTotal,
                        'profit' => $lineProfit,
                    ];
                }

                $discount = ($subtotal > 400 && rand(0, 4) === 0) ? 50 : 0;
                $grandTotal = max(0, $subtotal - $discount);

                $orderNumber = sprintf('FC-%s-%04d', $date->format('Ymd'), $orderCounter++);

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_id' => $customer?->id,
                    'user_id' => ($o % 2 === 0) ? $owner->id : $staff->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount' => 0,
                    'total_amount' => $grandTotal,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'order_status' => 'completed',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $itemData['order_id'] = $order->id;
                    $itemData['created_at'] = $createdAt;
                    $itemData['updated_at'] = $createdAt;
                    OrderItem::create($itemData);
                }

                // Payment record
                $txnId = match ($paymentMethod) {
                    'bkash' => 'BK'.strtoupper(substr(md5(uniqid()), 0, 8)),
                    'nagad' => 'NG'.strtoupper(substr(md5(uniqid()), 0, 8)),
                    'rocket' => 'RK'.strtoupper(substr(md5(uniqid()), 0, 8)),
                    'card' => 'POS-'.rand(100000, 999999),
                    default => null,
                };

                Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $customer?->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $grandTotal,
                    'transaction_id' => $txnId,
                    'reference' => 'POS Counter Checkout',
                    'status' => 'completed',
                    'payment_date' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Update customer totals
                if ($customer) {
                    $customer->increment('total_orders');
                    $customer->increment('total_spent', $grandTotal);
                    $customer->update(['last_order_at' => $createdAt]);

                    // Add loyalty points (৳100 = 1 pt)
                    $earnedPoints = (int) floor($grandTotal / 100);
                    if ($earnedPoints > 0) {
                        $customer->increment('loyalty_points', $earnedPoints);
                        LoyaltyPoint::create([
                            'customer_id' => $customer->id,
                            'order_id' => $order->id,
                            'points' => $earnedPoints,
                            'type' => 'earned',
                            'description' => 'Earned from order #'.$order->order_number,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }

            // Seed 1-2 Expenses per day
            $dailyExpenseCat = $expenseCategories[array_rand($expenseCategories)];
            $expenseAmount = rand(300, 1500);
            Expense::create([
                'expense_category_id' => $dailyExpenseCat->id,
                'user_id' => $owner->id,
                'description' => 'Daily operational fee: '.$dailyExpenseCat->name,
                'amount' => $expenseAmount,
                'payment_method' => 'cash',
                'date' => $date,
                'created_at' => $date->copy()->setTime(11, 0),
                'updated_at' => $date->copy()->setTime(11, 0),
            ]);

            // Seed Food Waste every 2-3 days
            if ($day % 2 === 0) {
                $wastedFood = $foods[array_rand($foods)];
                $wasteQty = rand(1, 3);
                $wasteCost = $wasteQty * $wastedFood->cost_price;
                $reasons = ['burned', 'overproduction', 'expired', 'spoiled'];
                Waste::create([
                    'food_id' => $wastedFood->id,
                    'user_id' => $staff->id,
                    'quantity' => $wasteQty,
                    'unit' => $wastedFood->unit,
                    'estimated_cost' => $wasteCost,
                    'reason' => $reasons[array_rand($reasons)],
                    'notes' => 'End of shift kitchen loss count',
                    'date' => $date,
                    'created_at' => $date->copy()->setTime(23, 15),
                    'updated_at' => $date->copy()->setTime(23, 15),
                ]);
            }

            // Seed Daily Closing Report for past 7 days (except today)
            if ($day > 0 && $day <= 7) {
                $dayOrders = Order::whereDate('created_at', $date)->where('order_status', 'completed')->get();
                $daySales = $dayOrders->sum('total_amount');
                $dayExpense = Expense::whereDate('date', $date)->sum('amount');
                $dayWaste = Waste::whereDate('date', $date)->sum('estimated_cost');
                $dayProfit = $daySales - $dayExpense - $dayWaste;
                $margin = ($daySales > 0) ? round(($dayProfit / $daySales) * 100, 1) : 0;

                $cashSales = Payment::whereDate('payment_date', $date)->where('payment_method', 'cash')->sum('amount');

                DailyReport::create([
                    'report_date' => $date,
                    'total_orders' => $dayOrders->count(),
                    'total_customers' => $dayOrders->pluck('customer_id')->filter()->unique()->count(),
                    'total_sales' => $daySales,
                    'cash_sales' => $cashSales,
                    'bkash_sales' => Payment::whereDate('payment_date', $date)->where('payment_method', 'bkash')->sum('amount'),
                    'nagad_sales' => Payment::whereDate('payment_date', $date)->where('payment_method', 'nagad')->sum('amount'),
                    'rocket_sales' => Payment::whereDate('payment_date', $date)->where('payment_method', 'rocket')->sum('amount'),
                    'card_sales' => Payment::whereDate('payment_date', $date)->where('payment_method', 'card')->sum('amount'),
                    'total_expenses' => $dayExpense,
                    'total_waste' => $dayWaste,
                    'net_profit' => $dayProfit,
                    'profit_margin' => $margin,
                    'is_closed' => true,
                    'closed_by' => $owner->id,
                    'closed_at' => $date->copy()->setTime(23, 45),
                    'notes' => 'Day closed with all gas cylinders locked and register balanced.',
                ]);
            }
        }

        // 11. Seed a sample stock purchase order
        $purchase = Purchase::create([
            'purchase_number' => 'PO-'.date('Ymd').'-0001',
            'supplier_id' => $suppliers[0]->id,
            'user_id' => $owner->id,
            'total_amount' => 4500.00,
            'paid_amount' => 4500.00,
            'due_amount' => 0.00,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'purchase_date' => Carbon::today()->subDays(2),
            'notes' => 'Fresh chicken and beef cuts from Karwan Bazar',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'food_id' => $foods[4]->id, // Crispy Chicken Wings
            'item_name' => 'Broiler Chicken Cuts',
            'quantity' => 20,
            'unit' => 'kg',
            'unit_price' => 225.00,
            'total_price' => 4500.00,
        ]);
    }
}
