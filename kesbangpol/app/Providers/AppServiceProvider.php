<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Asia/Jakarta');
        \Carbon\Carbon::setLocale('id');

        if (!app()->runningInConsole()) {
            view()->composer('*', function () {
                if (Auth::check()) {
                    /** @var User $user */
                    $user = Auth::user();
                    if (!$user->last_activity_at || $user->last_activity_at->diffInSeconds(now()) >= 60) {
                        $user->forceFill(['last_activity_at' => now()])->save();
                    }
                }
            });
        }
    }
}
