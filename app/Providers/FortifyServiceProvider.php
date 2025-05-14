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
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpKernel\Attribute\Cache;

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

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            Log::info("L'utente $throttleKey ha effettuato un tentativo di login.");
            return Limit::perMinute(5)->by($throttleKey)->response(function () use ($throttleKey) {
                // Log the blocked IP
                \Log::warning("Blocked IP: $throttleKey due to too many requests.");
        
                // Custom response when rate limit is exceeded
                return response()->json([
                    'message' => 'Too many requests. Please try again later.'
                ], 429);
            });
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
