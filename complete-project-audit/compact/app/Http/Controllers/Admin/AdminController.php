<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
abstract class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth:admin',
            'admin',
        ];
    }
}