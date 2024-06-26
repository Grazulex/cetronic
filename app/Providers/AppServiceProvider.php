<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMetaTranslation;
use App\Models\CategoryTranslation;
use App\Models\Item;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use App\Models\UserBrand;
use App\Observers\BrandObserver;
use App\Observers\CategoryMetaTranslationObserver;
use App\Observers\CategoryObserver;
use App\Observers\CategoryTranslationObserver;
use App\Observers\ItemObserver;
use App\Observers\LocationObserver;
use App\Observers\OrderObserver;
use App\Observers\UserBrandObserver;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Blade::directive('money', fn ($amount) => "<?php echo number_format({$amount}, 2, ',', '.'). '€'; ?>");
        Brand::observe(BrandObserver::class);
        Category::observe(CategoryObserver::class);
        Item::observe(ItemObserver::class);
        CategoryTranslation::observe(CategoryTranslationObserver::class);
        CategoryMetaTranslation::observe(CategoryMetaTranslationObserver::class);
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);
        Location::observe(LocationObserver::class);
        UserBrand::observe(UserBrandObserver::class);

        $this->bootRoute();
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));


    }
}
