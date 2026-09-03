<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function loginAs(Request $request, string $role): RedirectResponse
    {
        $normalizedRole = in_array($role, ['staff', 'cashier']) ? 'staff' : 'owner';
        $view = $request->query('view');
        if ($view) {
            session(['view_mode' => $view]);
        } else {
            session(['view_mode' => 'mobile']);
        }

        $user = User::where('role', $normalizedRole)->first()
            ?? User::where('email', "{$normalizedRole}@foodcart.test")->first()
            ?? User::where('email', "{$normalizedRole}@foodcart360.com")->first();

        if (! $user) {
            $user = User::create([
                'name' => $normalizedRole === 'owner' ? 'Demo Owner' : 'Demo Staff',
                'email' => "{$normalizedRole}@foodcart.test",
                'password' => bcrypt('password'),
                'role' => $normalizedRole,
                'is_active' => true,
            ]);
        }

        Auth::login($user);

        $params = $view ? ['view' => $view] : [];

        if ($user->isOwner()) {
            return redirect()->route('dashboard', $params)->with('success', "ওনার হিসেবে লগইন সম্পন্ন ({$user->name})");
        }

        return redirect()->route('cartboy.index', $params)->with('success', "কার্টবয় / স্টাফ হিসেবে লগইন সম্পন্ন ({$user->name})");
    }
}
