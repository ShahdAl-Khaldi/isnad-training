<?php

namespace App\Providers;

use App\Models\Application;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // حدود منفصلة لكل عملية (طلاب الجامعة ممكن يكونوا على نفس الإنترنت)
        RateLimiter::for('apply', fn (Request $r) => Limit::perMinute(20)->by('apply|'.$r->ip()));
        RateLimiter::for('track', fn (Request $r) => Limit::perMinute(20)->by('track|'.$r->ip()));
        RateLimiter::for('student-action', fn (Request $r) => Limit::perMinute(10)->by('act|'.$r->route('token')));
        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by('login|'.strtolower((string) $r->input('email')).'|'.$r->ip()),
            Limit::perMinute(30)->by('login-ip|'.$r->ip()),
        ]);

        // أرقام القائمة الجانبية بلوحة إسناد
        View::composer('layouts.admin', function ($view) {
            $view->with('navCounts', [
                'payments' => Application::where('status', Application::PAYMENT_SUBMITTED)->count(),
                'new' => Application::where('status', Application::SUBMITTED)->count(),
            ]);
        });
    }
}
