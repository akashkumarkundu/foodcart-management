<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class DailyClosingService
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    /**
     * Get closing preview for today or specified date
     */
    public function getClosingPreview(?Carbon $date = null): array
    {
        $date = $date ? $date->copy() : Carbon::today();
        $summary = $this->financialService->getSummary($date, $date);
        $timeline = $this->financialService->getSalesTimeline($date, $date);
        $itemWise = $this->financialService->getItemWiseSales($date, $date);
        $cartRent = (float) Setting::get('daily_cart_rent', 0.0);

        $existingReport = DailyReport::where('report_date', $date->toDateString())->first();

        return array_merge($summary, [
            'total_sales' => $summary['completed_sales'],
            'waste' => $summary['total_waste'],
            'expenses' => $summary['total_expenses'],
            'is_already_closed' => (bool) $existingReport?->is_closed,
            'existing_report' => $existingReport,
            'sales_timeline' => $timeline,
            'item_wise_sales' => $itemWise,
            'cart_rent' => $cartRent,
            'cart_boy_net' => max(0, $summary['net_profit'] - $cartRent),
            'cart_boy_net_income' => max(0, $summary['net_profit'] - $cartRent),
            'expected_cash' => (float) ($summary['payment_breakdown']['cash'] ?? 0.0),
            'is_cart_open' => (bool) Setting::get('is_cart_open', true),
        ]);
    }

    /**
     * Close the business day and store immutable snapshot
     */
    public function closeDay(
        Carbon $date,
        User $user,
        ?string $notes = null,
        ?float $physicalCash = null
    ): DailyReport {
        $dateStr = $date->toDateString();
        $existing = DailyReport::whereDate('report_date', $dateStr)->first();

        if ($existing && $existing->is_closed) {
            throw new Exception("The business day for {$dateStr} is already closed. Please reopen it first if modifications are required.");
        }

        $summary = $this->financialService->getSummary($date, $date);

        $closingNotes = $notes ?? '';
        if ($physicalCash !== null) {
            $expectedCash = $summary['payment_breakdown']['cash'] ?? 0;
            $cashDiff = $physicalCash - $expectedCash;
            $closingNotes .= ($closingNotes ? ' | ' : '').'Physical Cash Count: ৳'.number_format($physicalCash, 2).
                ' (Variance: ৳'.number_format($cashDiff, 2).')';
        }

        $report = $existing ?: new DailyReport(['report_date' => $dateStr]);
        $report->fill([
            'total_orders' => $summary['total_orders'],
            'total_customers' => $summary['total_customers'],
            'total_sales' => $summary['completed_sales'],
            'cash_sales' => $summary['payment_breakdown']['cash'] ?? 0.0,
            'bkash_sales' => $summary['payment_breakdown']['bkash'] ?? 0.0,
            'nagad_sales' => $summary['payment_breakdown']['nagad'] ?? 0.0,
            'rocket_sales' => $summary['payment_breakdown']['rocket'] ?? 0.0,
            'card_sales' => $summary['payment_breakdown']['card'] ?? 0.0,
            'total_expenses' => $summary['total_expenses'],
            'total_waste' => $summary['total_waste'],
            'net_profit' => $summary['net_profit'],
            'profit_margin' => $summary['profit_margin'],
            'closed_by' => $user->id,
            'closed_at' => now(),
            'notes' => $closingNotes,
            'is_closed' => true,
        ]);
        $report->save();

        // System notification
        Notification::create([
            'type' => 'daily_closing',
            'title' => "🔒 Daily Business Closed for {$date->format('d M Y')}",
            'message' => 'Total Sales: ৳'.number_format($report->total_sales, 2).' | Net Profit: ৳'.number_format($report->net_profit, 2)." (Closed by {$user->name})",
            'link' => route('closing.index'),
            'is_read' => false,
        ]);

        return $report;
    }

    /**
     * Reopen day (Owner only)
     */
    public function reopenDay(Carbon $date, User $user, ?string $reason = null): DailyReport
    {
        $dateStr = $date->toDateString();
        $report = DailyReport::whereDate('report_date', $dateStr)->firstOrFail();

        return $this->reopenReport($report, $user, $reason);
    }

    /**
     * Reopen specific DailyReport instance
     */
    public function reopenReport(DailyReport $report, User $user, ?string $reason = null): DailyReport
    {
        $report->update([
            'is_closed' => false,
            'notes' => ($report->notes ? $report->notes.' | ' : '')."Reopened by {$user->name} on ".now()->format('Y-m-d H:i').($reason ? " (Reason: {$reason})" : ''),
        ]);

        return $report;
    }
}
