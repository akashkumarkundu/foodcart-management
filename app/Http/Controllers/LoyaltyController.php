<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = LoyaltyPoint::with(['customer', 'order'])
            ->latest()
            ->paginate(15);

        $ratio = (float) Setting::get('loyalty_points_ratio', 100.0);
        $totalEarned = LoyaltyPoint::where('type', 'earned')->sum('points');
        $totalRedeemed = abs(LoyaltyPoint::where('type', 'redeemed')->sum('points'));

        $topLoyalCustomers = Customer::orderByDesc('loyalty_points')->take(10)->get();

        return view('loyalty.index', compact(
            'transactions',
            'ratio',
            'totalEarned',
            'totalRedeemed',
            'topLoyalCustomers'
        ));
    }

    public function updateRatio(Request $request): RedirectResponse
    {
        $request->validate([
            'loyalty_points_ratio' => ['required', 'numeric', 'min:10', 'max:5000'],
        ]);

        Setting::set('loyalty_points_ratio', $request->loyalty_points_ratio, 'float', 'loyalty');

        return back()->with('success', "Loyalty rule updated: Every ৳{$request->loyalty_points_ratio} spent gives 1 point.");
    }
}
