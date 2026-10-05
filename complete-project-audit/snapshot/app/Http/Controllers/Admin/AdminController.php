<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

abstract class AdminController extends Controller implements HasMiddleware
{
    /**
     * Middleware applied to every admin controller.
     *
     * Admin authentication must always use the dedicated "admin" guard.
     * Never use the default "auth" middleware here because that authenticates
     * against the customer/web guard.
     */
    public static function middleware(): array
    {
        return [
            'auth:admin',
            'admin',
        ];
    }
}