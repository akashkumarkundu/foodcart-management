<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Setting;
use App\Services\DailyClosingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    public function __construct(
        protected DailyClosingService $closingService
    ) {}

    public function index(Request $request): View
    {
        $selectedDate = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $preview = $this->closingService->getClosingPreview($selectedDate);

        $pastReports = DailyReport::with('closer')
            ->latest('report_date')
            ->paginate(10);

        return view('closing.index', compact('preview', 'selectedDate', 'pastReports'));
    }

    public function close(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'physical_cash' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($request->date);

        try {
            $report = $this->closingService->closeDay(
                $date,
                $request->user(),
                $request->notes,
                $request->filled('physical_cash') ? (float) $request->physical_cash : null
            );

            return back()->with('success', "Daily business for {$date->format('d M Y')} has been officially closed! Total Sales: ৳".number_format($report->total_sales, 2).', Net Profit: ৳'.number_format($report->net_profit, 2));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reopen(Request $request, DailyReport $dailyReport): RedirectResponse
    {
        try {
            $this->closingService->reopenReport(
                $dailyReport,
                $request->user(),
                $request->input('reason', 'Administrative revision requested by Owner')
            );

            return back()->with('success', "Business day for {$dailyReport->report_date->format('d M Y')} is reopened for adjustments.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleCartStatus(Request $request): RedirectResponse
    {
        $current = (bool) Setting::get('is_cart_open', true);
        $newStatus = ! $current;
        Setting::set('is_cart_open', $newStatus);

        $statusText = $newStatus ? 'কার্ট সফলভাবে খোলা হয়েছে (Cart is now Open)' : 'কার্ট সফলভাবে সাময়িক বন্ধ করা হয়েছে (Cart is now Closed)';

        return back()->with('success', $statusText);
    }
}
