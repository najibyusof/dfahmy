<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookableUnit;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\OperationAlertSetting;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Policies\AuditLogPolicy;
use App\Policies\BuildingPolicy;
use App\Policies\BookingPolicy;
use App\Policies\BookableUnitPolicy;
use App\Policies\GuestPolicy;
use App\Policies\HousekeepingTaskPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\RoomPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('notification-actions', static function (Request $request): Limit {
            return Limit::perMinute(60)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('report-exports', static function (Request $request): Limit {
            return Limit::perMinute(20)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('state-mutations', static function (Request $request): Limit {
            return Limit::perMinute(180)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('health-check', static function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->ip());
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(HousekeepingTask::class, HousekeepingTaskPolicy::class);
        Gate::policy(Building::class, BuildingPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Guest::class, GuestPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(BookableUnit::class, BookableUnitPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        Building::observe(AuditableObserver::class);
        Room::observe(AuditableObserver::class);
        Guest::observe(AuditableObserver::class);
        Booking::observe(AuditableObserver::class);
        BookableUnit::observe(AuditableObserver::class);
        Payment::observe(AuditableObserver::class);
        HousekeepingTask::observe(AuditableObserver::class);
        OperationAlertSetting::observe(AuditableObserver::class);

        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
