<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Waste;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Get financial metrics for a specific date or date range
     */
    public function getSummary(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? $endDate->copy()->endOfDay() : Carbon::today()->endOfDay();

        // Completed sales
        $ordersQuery = Order::where('order_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalOrders = (clone $ordersQuery)->count();
        $totalCustomers = (clone $ordersQuery)->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');
        $grossSales = (float) (clone $ordersQuery)->sum('subtotal');
        $totalDiscount = (float) (clone $ordersQuery)->sum('discount_amount');
        $completedSales = (float) (clone $ordersQuery)->sum('total_amount');

        // Total cost of goods sold from order items
        $cogs = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('order_items.cost_price * order_items.quantity'));

        // Expenses
        $totalExpenses = (float) Expense::whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->sum('amount');

        // Food waste cost
        $totalWaste = (float) Waste::whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->sum('estimated_cost');

        // Formula: Net Profit = Completed Sales - Expenses - Waste Cost
        $netProfit = $completedSales - $totalExpenses - $totalWaste;
        $profitMargin = $completedSales > 0 ? round(($netProfit / $completedSales) * 100, 2) : 0.0;

        // Payment method breakdown for completed orders
        $payments = Payment::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->where('order_status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })->where('status', 'completed')->get();

        $paymentBreakdown = [
            'cash' => (float) $payments->where('payment_method', 'cash')->sum('amount'),
            'bkash' => (float) $payments->where('payment_method', 'bkash')->sum('amount'),
            'nagad' => (float) $payments->where('payment_method', 'nagad')->sum('amount'),
            'rocket' => (float) $payments->where('payment_method', 'rocket')->sum('amount'),
            'card' => (float) $payments->where('payment_method', 'card')->sum('amount'),
        ];

        // Parcel vs Dine-in breakdown
        $parcelOrders = (clone $ordersQuery)->whereIn('order_type', ['parcel', 'takeaway'])->count();
        $parcelSales = (float) (clone $ordersQuery)->whereIn('order_type', ['parcel', 'takeaway'])->sum('total_amount');
        $dineInOrders = (clone $ordersQuery)->where('order_type', 'dine_in')->count();
        $dineInSales = (float) (clone $ordersQuery)->where('order_type', 'dine_in')->sum('total_amount');

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'gross_sales' => $grossSales,
            'total_discount' => $totalDiscount,
            'completed_sales' => $completedSales,
            'cogs' => $cogs,
            'total_expenses' => $totalExpenses,
            'total_waste' => $totalWaste,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'payment_breakdown' => $paymentBreakdown,
            'parcel_orders' => $parcelOrders,
            'parcel_sales' => $parcelSales,
            'dine_in_orders' => $dineInOrders,
            'dine_in_sales' => $dineInSales,
        ];
    }

    /**
     * Get chronological sales timeline showing individually which item sold at what exact time and price
     */
    public function getSalesTimeline(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? $endDate->copy()->endOfDay() : Carbon::today()->endOfDay();

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('orders.order_status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.id as order_id',
                'orders.order_number',
                'orders.created_at',
                'orders.payment_method',
                'orders.order_type',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'order_items.food_id',
                'order_items.food_name',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.cost_price',
                'order_items.subtotal',
                'order_items.profit'
            )
            ->orderByDesc('orders.created_at')
            ->get()
            ->map(function ($row) {
                $orderTypeBn = match ($row->order_type) {
                    'parcel', 'takeaway' => 'পার্সেল',
                    'dine_in' => 'বসে খাওয়া',
                    default => 'কাউন্টার',
                };

                return [
                    'order_id' => $row->order_id,
                    'order_number' => $row->order_number,
                    'time' => $row->created_at,
                    'created_at' => $row->created_at,
                    'formatted_time' => Carbon::parse($row->created_at)->format('h:i:s A'),
                    'time_diff' => Carbon::parse($row->created_at)->diffForHumans(),
                    'payment_method' => $row->payment_method,
                    'order_type' => $row->order_type,
                    'order_type_bn' => $orderTypeBn,
                    'customer_name' => $row->customer_name,
                    'customer_phone' => $row->customer_phone,
                    'food_id' => $row->food_id,
                    'food_name' => $row->food_name,
                    'quantity' => (int) $row->quantity,
                    'unit_price' => (float) $row->unit_price,
                    'cost_price' => (float) $row->cost_price,
                    'subtotal' => (float) $row->subtotal,
                    'profit' => (float) $row->profit,
                ];
            });
    }

    /**
     * Get item-wise sales summary for a date or date range
     */
    public function getItemWiseSales(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? $endDate->copy()->endOfDay() : Carbon::today()->endOfDay();

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'order_items.food_id',
                'order_items.food_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('AVG(order_items.unit_price) as avg_price'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.profit) as total_profit')
            )
            ->groupBy('order_items.food_id', 'order_items.food_name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($row) {
                return [
                    'food_id' => $row->food_id,
                    'food_name' => $row->food_name,
                    'quantity' => (int) $row->total_quantity,
                    'total_quantity' => (int) $row->total_quantity,
                    'avg_price' => (float) $row->avg_price,
                    'revenue' => (float) $row->total_revenue,
                    'total_revenue' => (float) $row->total_revenue,
                    'profit' => (float) $row->total_profit,
                    'total_profit' => (float) $row->total_profit,
                ];
            });
    }

    /**
     * Get today's top-level dashboard metrics
     */
    public function getTodayMetrics(): array
    {
        return $this->getSummary(Carbon::today(), Carbon::today());
    }

    /**
     * Get last 7 days daily sales, expenses, waste, profit for charts
     */
    public function getLast7DaysTrend(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $daySummary = $this->getSummary($date, $date);

            $days[] = [
                'date' => $date->format('M d'),
                'day_name' => $date->format('D'),
                'sales' => $daySummary['completed_sales'],
                'expenses' => $daySummary['total_expenses'],
                'waste' => $daySummary['total_waste'],
                'profit' => $daySummary['net_profit'],
                'orders' => $daySummary['total_orders'],
            ];
        }

        return $days;
    }

    /**
     * Get hourly sales distribution for today (e.g. 10 AM to 11 PM)
     */
    public function getTodayHourlySales(): array
    {
        $today = Carbon::today();
        $hours = [];

        // Peak times for Bangladeshi food carts: 10:00 to 23:00
        for ($h = 10; $h <= 23; $h++) {
            $start = $today->copy()->hour($h)->minute(0)->second(0);
            $end = $today->copy()->hour($h)->minute(59)->second(59);

            $sales = (float) Order::where('order_status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');

            $ordersCount = Order::where('order_status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $displayHour = $start->format('g A');

            $hours[] = [
                'hour' => $displayHour,
                'raw_hour' => $h,
                'sales' => $sales,
                'orders' => $ordersCount,
            ];
        }

        return $hours;
    }

    /**
     * Get monthly sales & expenses for the current year
     */
    public function getMonthlyBreakdown(int $year): array
    {
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $summary = $this->getSummary($start, $end);

            $months[] = [
                'month' => $start->format('M'),
                'sales' => $summary['completed_sales'],
                'expenses' => $summary['total_expenses'],
                'waste' => $summary['total_waste'],
                'profit' => $summary['net_profit'],
            ];
        }

        return $months;
    }

    /**
     * Top selling foods for a date range
     */
    public function getTopSellingFoods(int $limit = 5, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ? $startDate->copy()->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $endDate ? $endDate->copy()->endOfDay() : Carbon::now()->endOfDay();

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'order_items.food_id',
                'order_items.food_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.profit) as total_profit')
            )
            ->groupBy('order_items.food_id', 'order_items.food_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }
}
