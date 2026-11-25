<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enum\UserRoleEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // An order's PDF and pictures zip: its customer, or the shop staff.
        Gate::define('download-order', fn(User $user, Order $order): bool => $user->id === $order->user_id || in_array($user->role, [UserRoleEnum::ADMIN, UserRoleEnum::AGENT], true));
    }
}
