<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('article-search', function (Request $request) {
            $ipAddress = $request->ip();

            // Check if the IP is already blocked
            if (Cache::has("blocked:$ipAddress")) {
                abort(429, "Too many requests. Please try again later.");
            }

            // Define the rate limit - 10 requests per minute per IP
            $rateLimit = Limit::perMinute(20)->by($ipAddress);

            // Block the IP for 10 min if limit is exceeded
            if ($rateLimit->tooManyAttempts()) {
                // log the blocked IP
                \Log::warning("Blocked IP: $ipAddress due to too many requests.");
                Cache::put("blocked:$ipAddress", true, now()->addMinutes(10));
            }

            return $rateLimit;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        RateLimiter::for('carrers', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
        
        RateLimiter::for('submit', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
