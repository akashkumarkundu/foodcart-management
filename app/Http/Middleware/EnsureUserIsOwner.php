<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOwner()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Owner access required.'], 403);
            }

            return redirect()->route('cartboy.index')
                ->with('error', 'এই সেকশনটি শুধুমাত্র ওনারের জন্য সংরক্ষিত। আপনি কার্টবয় প্যানেলে আছেন।');
        }

        return $next($request);
    }
}
