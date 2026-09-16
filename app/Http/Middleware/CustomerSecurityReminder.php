<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerSecurityReminder
{
    /**
     * Show customer security setup reminder
     * no more than once per calendar day.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Ignore Guests / Admins / Disabled Customers
        |--------------------------------------------------------------------------
        */

        if (
            ! $user ||
            $user->is_admin ||
            $user->is_super_admin ||
            $user->status !== 'active'
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Nothing Missing
        |--------------------------------------------------------------------------
        */

        if (! $user->needsSecuritySetup()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Already Shown Today
        |--------------------------------------------------------------------------
        */

        if (! $user->shouldShowSecurityReminder()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Phone-Created Account Needs Backup
        |--------------------------------------------------------------------------
        */

        if ($user->needsBackupLoginMethod()) {
            $reminder = [
                'type' =>
                    'backup',

                'title' =>
                    'Secure your account',

                'message' =>
                    'Add a verified email, Google or Facebook account as a backup login method so you can recover your account if you lose access to your phone.',

                'button' =>
                    'Add backup method',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Email / Social Account Needs Phone
        |--------------------------------------------------------------------------
        */

        else {
            $reminder = [
                'type' =>
                    'phone',

                'title' =>
                    'Add your phone number',

                'message' =>
                    'Add and verify your phone number to improve account security and give you another way to access your account.',

                'button' =>
                    'Add phone number',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Pass Reminder To Blade Layout
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'customerSecurityReminder',
            $reminder
        );

        /*
        |--------------------------------------------------------------------------
        | Mark Reminder As Shown
        |--------------------------------------------------------------------------
        */

        $user->markSecurityReminderAsShown();

        return $next($request);
    }
}