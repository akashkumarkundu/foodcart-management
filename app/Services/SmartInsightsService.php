<?php

namespace App\Services;

use App\Models\Food;
use App\Models\Order;
use App\Models\Waste;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SmartInsightsService
{
    /**
     * Generate list of intelligent business insights from active database records
     */
    public function generateInsights(): array
    {
        $insights = [];
        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // 1. Most Profitable Food Item This Month
        $mostProfitable = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $thisMonthStart)
            ->select('order_items.food_name', DB::raw('SUM(order_items.profit) as total_profit'), DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.food_name')
            ->orderByDesc('total_profit')
            ->first();

        if ($mostProfitable && $mostProfitable->total_profit > 0) {
            $insights[] = [
                'type' => 'success',
                'category' => 'Profitability',
                'icon' => 'sparkles',
                'title' => "Highest Profit Leader: {$mostProfitable->food_name}",
                'description' => "{$mostProfitable->food_name} generated ৳".number_format($mostProfitable->total_profit, 2)." in net margin this month across {$mostProfitable->total_qty} units sold.",
                'action' => 'Highlight this item as a Chef Special on the menu to drive maximum cart profitability.',
            ];
        }

        // 2. Best-Selling Food Item (Volume)
        $topVolume = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $now->copy()->subDays(30))
            ->select('order_items.food_name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.food_name')
            ->orderByDesc('total_qty')
            ->first();

        if ($topVolume) {
            $insights[] = [
                'type' => 'info',
                'category' => 'Popularity',
                'icon' => 'trophy',
                'title' => "Customer Favorite: {$topVolume->food_name}",
                'description' => "{$topVolume->food_name} is your #1 volume driver with {$topVolume->total_qty} portions served in the last 30 days.",
                'action' => 'Ensure raw ingredients for this item are always in stock to prevent stockout during peak rush hours.',
            ];
        }

        // 3. Peak Sales Rush Hour Detection
        $hourlySales = DB::table('orders')
            ->where('order_status', 'completed')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->select(
                DB::raw('strftime("%H", created_at) as hour_of_day'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('hour_of_day')
            ->orderByDesc('revenue')
            ->first();

        if ($hourlySales && $hourlySales->hour_of_day !== null) {
            $hourInt = (int) $hourlySales->hour_of_day;
            $startHourStr = Carbon::createFromTime($hourInt)->format('g A');
            $endHourStr = Carbon::createFromTime($hourInt + 1)->format('g A');

            $insights[] = [
                'type' => 'primary',
                'category' => 'Sales Trends',
                'icon' => 'clock',
                'title' => "Peak Rush Hour: {$startHourStr} - {$endHourStr}",
                'description' => "Your cart generates peak sales between {$startHourStr} and {$endHourStr} (৳".number_format($hourlySales->revenue, 2).' over 30 days).',
                'action' => 'Pre-cook or pre-prep bulk ingredients (patties, kebabs, rice) by '.Carbon::createFromTime(max(0, $hourInt - 1))->format('g A').' to serve customers rapidly.',
            ];
        }

        // 4. Highest Waste Food Item & Reason
        $highestWaste = DB::table('wastes')
            ->join('foods', 'wastes.food_id', '=', 'foods.id')
            ->where('wastes.date', '>=', $now->copy()->subDays(30)->toDateString())
            ->select('foods.name as food_name', 'wastes.reason', DB::raw('SUM(wastes.estimated_cost) as total_waste_cost'), DB::raw('SUM(wastes.quantity) as total_qty'))
            ->groupBy('foods.name', 'wastes.reason')
            ->orderByDesc('total_waste_cost')
            ->first();

        if ($highestWaste && $highestWaste->total_waste_cost > 0) {
            $reasonText = ucfirst($highestWaste->reason);
            $insights[] = [
                'type' => 'warning',
                'category' => 'Waste Reduction',
                'icon' => 'trash',
                'title' => "High Waste Alert: {$highestWaste->food_name}",
                'description' => 'Lost ৳'.number_format($highestWaste->total_waste_cost, 2)." on {$highestWaste->food_name} mainly due to {$reasonText}.",
                'action' => "Audit portion sizes and batch preparation schedules for {$highestWaste->food_name} to curb raw cost leakage.",
            ];
        }

        // 5. Month-Over-Month Sales Growth
        $thisMonthSales = (float) Order::where('order_status', 'completed')
            ->where('created_at', '>=', $thisMonthStart)
            ->sum('total_amount');

        $lastMonthSales = (float) Order::where('order_status', 'completed')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_amount');

        if ($lastMonthSales > 0) {
            $growthPct = round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1);
            $isPositive = $growthPct >= 0;

            $insights[] = [
                'type' => $isPositive ? 'success' : 'danger',
                'category' => 'Revenue Growth',
                'icon' => $isPositive ? 'arrow-trending-up' : 'arrow-trending-down',
                'title' => ($isPositive ? 'Sales Growth: +' : 'Sales Decline: ')."{$growthPct}% vs Last Month",
                'description' => 'Current month sales stand at ৳'.number_format($thisMonthSales, 2).' compared to ৳'.number_format($lastMonthSales, 2).' last month.',
                'action' => $isPositive
                    ? 'Momentum is strong! Consider running weekend combo offers to accelerate cart turnover.'
                    : 'Consider introducing introductory snack combos or loyalty discounts to re-engage past customers.',
            ];
        }

        // 6. Underperforming / Least-Selling Food Item
        $leastSelling = Food::where('is_active', true)
            ->whereDoesntHave('orderItems', function ($q) use ($now) {
                $q->where('created_at', '>=', $now->copy()->subDays(14));
            })
            ->first();

        if ($leastSelling) {
            $insights[] = [
                'type' => 'neutral',
                'category' => 'Menu Engineering',
                'icon' => 'archive-box-x-mark',
                'title' => "Zero Sales in 14 Days: {$leastSelling->name}",
                'description' => "No customer orders recorded for {$leastSelling->name} ({$leastSelling->bengali_name}) in the last two weeks.",
                'action' => 'Evaluate whether to repurpose ingredients or replace this menu item with a seasonal Bangladeshi snack.',
            ];
        }

        return $insights;
    }
}
