<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class CustomerAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('web')->user();
        if (!$customer || (!$customer->is_admin && $customer->status === 'active')) return $next($request);
        // Revoke only customer authentication; keep an independent admin session.
        Auth::guard('web')->logout();
        if ($request->hasSession()) $request->session()->regenerateToken();
        if ($request->is('admin', 'admin/*')) return $next($request);
        if ($request->expectsJson()) return response()->json(['message' => 'This customer account is currently disabled.'], 403);
        return redirect()->route('login')->withErrors(['email' => 'This customer account is currently disabled.']);
    }
}