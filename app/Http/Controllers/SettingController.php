<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'cart_name' => Setting::get('cart_name', 'রেশম নগরী বাইটস (Resham Nogori Bites)'),
            'cart_address' => Setting::get('cart_address', 'টি-বাঁধ সংলগ্ন, পদ্মা গার্ডেন রোড, রাজশাহী'),
            'cart_phone' => Setting::get('cart_phone', '01712-345678'),
            'cart_email' => Setting::get('cart_email', 'info@foodcart360.com'),
            'currency' => Setting::get('currency', 'BDT'),
            'currency_symbol' => Setting::get('currency_symbol', '৳'),
            'timezone' => Setting::get('timezone', 'Asia/Dhaka'),
            'theme' => Setting::get('theme', 'modern-light'),
            'tax_rate' => Setting::get('tax_rate', 0.0),
            'loyalty_points_ratio' => Setting::get('loyalty_points_ratio', 100.0),
            'low_stock_threshold' => Setting::get('low_stock_threshold', 10),
            'receipt_footer' => Setting::get('receipt_footer', 'ধন্যবাদ! রেশম নগরী বাইটসে আবার আসবেন। (Visit Us Again!)'),
        ];

        $availableThemes = [
            [
                'id' => 'modern-light',
                'name' => 'Modern Light',
                'description' => 'Crisp clean white background with emerald accents.',
                'primary' => '#10b981',
                'bg' => '#f8fafc',
            ],
            [
                'id' => 'dark-mode',
                'name' => 'Dark Mode',
                'description' => 'High contrast slate-900 dark theme for night stalls.',
                'primary' => '#10b981',
                'bg' => '#090d16',
            ],
            [
                'id' => 'warm-food',
                'name' => 'Warm Food',
                'description' => 'Spicy paprika, terracotta and warm culinary tones.',
                'primary' => '#ea580c',
                'bg' => '#fdf8f4',
            ],
            [
                'id' => 'fresh-restaurant',
                'name' => 'Fresh Restaurant',
                'description' => 'Mint and forest emerald freshness for healthy food carts.',
                'primary' => '#059669',
                'bg' => '#f0fdf4',
            ],
            [
                'id' => 'premium-black',
                'name' => 'Premium Black',
                'description' => 'Pitch obsidian and luxury metallic gold accents.',
                'primary' => '#d4af37',
                'bg' => '#050505',
            ],
            [
                'id' => 'bangladesh',
                'name' => 'Bangladesh Inspired',
                'description' => 'Deep bottle green & ruby crimson national pride palette.',
                'primary' => '#006a4e',
                'bg' => '#f2f7f4',
            ],
        ];

        return view('settings.index', compact('settings', 'availableThemes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_name' => ['required', 'string', 'max:255'],
            'cart_address' => ['nullable', 'string', 'max:500'],
            'cart_phone' => ['nullable', 'string', 'max:20'],
            'cart_email' => ['nullable', 'email', 'max:255'],
            'theme' => ['required', 'string', 'in:modern-light,dark-mode,warm-food,fresh-restaurant,premium-black,bangladesh'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'loyalty_points_ratio' => ['nullable', 'numeric', 'min:1'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:1'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Set cookie so browser updates immediately
        cookie()->queue('fc_theme', $validated['theme'], 60 * 24 * 365);

        return back()->with('success', 'Food Cart settings updated successfully.');
    }

    public function switchTheme(Request $request): JsonResponse|RedirectResponse
    {
        $theme = $request->input('theme', 'modern-light');

        if (! in_array($theme, ['modern-light', 'dark-mode', 'warm-food', 'fresh-restaurant', 'premium-black', 'bangladesh'])) {
            $theme = 'modern-light';
        }

        Setting::set('theme', $theme);
        cookie()->queue('fc_theme', $theme, 60 * 24 * 365);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'theme' => $theme]);
        }

        return back()->with('success', 'Theme changed successfully.');
    }
}
