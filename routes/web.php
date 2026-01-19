<?php

declare(strict_types=1);

use App\Exports\ItemsExport;
use App\Http\Controllers\B2bController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Contact\SendContactController;
use App\Http\Controllers\Help\SendHelpController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\User\UpdatePasswordController;
use App\Http\Controllers\User\UpdateUserController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Location;
use App\Models\Order;
use App\Services\BrandService;
use App\Services\ItemService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('imager/{src?}', function ($src) {
    $cacheimage = Image::cache(fn($image) => $image->make('files/image/' . $src)->resize(60, 60), 10, true);

    return Response::make($cacheimage, 200, ['Content-Type' => 'image/jpeg']);
});


Route::get('orders/zip/{order}', function (Order $order, OrderService $orderService) {
    return Response()->download(
        $orderService->getPicturesZip($order),
        mb_strtoupper($order->reference) . '.zip',
        ['Content-type' => 'application/zip'],
    );
})->name('user_orders.zip');
Route::get('items/zip/{item}', function (Item $item, ItemService $itemService) {
    return Response()->download(
        $itemService->getPicturesZip($item),
        mb_strtoupper($item->slug) . '.jpeg',
        ['Content-type' => 'media/jpeg'],
    );
})->name('item.zip');
Route::get('orders/pdf/{order}', function (Order $order, OrderService $orderService) {
    return response()->streamDownload(function () use ($order, $orderService): void {
        echo $orderService->getPdf($order);
    }, mb_strtoupper($order->reference) . '.pdf');
})->name('user_orders.pdf');
Route::get('order/thanks', fn() => view('front.pages.order.thanks'))->name('order.thanks');
Route::view('register/thanks', 'front.users.thanks')->name('user_thanks');

Route::get('brands/download_agent', fn() => Excel::download(new ItemsExport(), 'cetronic-items-catalog.xlsx'))->name('brand.download.agent');

Route::get('brands/download/{brand}/{catSlug}', function (Brand $brand, string $slug, BrandService $brandService) {
    return response()->streamDownload(function () use ($brand, $slug, $brandService): void {
        echo $brandService->getCatalog($brand, $slug, auth()->user());
    }, mb_strtoupper($brand->name) . '.pdf');
})->name('brand.download.catalog');

Route::get('brands/download_zip/{brand}/{catSlug}', function (Brand $brand, string $slug, BrandService $brandService) {
    return Response()->download(
        $brandService->getPictures($brand, $slug),
        mb_strtoupper($brand->slug) . '.zip',
        ['Content-type' => 'application/zip'],
    );
})->name('brand.download.zip');

/*
|--------------------------------------------------------------------------
| B2B API Routes
|--------------------------------------------------------------------------
*/
Route::post('b2b/signup', [B2bController::class, 'signup'])->name('b2b.signup');

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [
            LocaleCookieRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ],
    ],
    function (): void {
        Route::view('/', 'front.pages.home')->name('home');
        Route::get('pdf', [PdfController::class, 'index'])->name('pdf');
        Route::view('about', 'front.pages.about')->name('about');
        Route::any('search', [ListingController::class, 'search'])->name('search')->methods('get', 'post');
        Route::view('contact', 'front.pages.contact')->name('contact');
        Route::post('contact-send', SendContactController::class)->name('contact.send');
        Route::view('contact/thanks', 'front.pages.contact-thanks')->name('contact.thanks');
        Route::view('help', 'front.pages.help')->name('help');
        Route::post('help-send', SendHelpController::class)->name('help.send');
        Route::get('cart', [CartController::class, 'index'])->name('cart');
        Route::get('item/{item:slug}', fn(Item $item) => view('front.pages.item', ['item' => $item]))->name('item');

        Route::group(
            [
                'prefix' => 'cart',
                'middleware' => [Authenticate::class],
            ],
            function (): void {
                Route::get('{cart}/locations', fn(Cart $cart) => view('front.pages.cart.locations', ['cart' => $cart]))->name('cart.locations');
                Route::controller(CartController::class)->group(function (): void {
                    Route::get('{cart}/confirm', 'confirm')->name('cart.confirm');
                    Route::get('{cart}/store', 'store')->name('cart.store');
                });
            },
        );

        Route::group(
            [
                'prefix' => 'user',
                'middleware' => [Authenticate::class],
            ],
            function (): void {
                Route::view('dashboard', 'front.users.dashboard')->name('user_dashboard');
                Route::view('profile', 'front.users.profile')->name('user_profile');
                Route::post('profile', UpdateUserController::class)->name('user_profile.update');
                Route::view('orders', 'front.users.orders')->name('user_orders');
                Route::get('orders/content/{order}', fn(Order $order) => view('front.users.orders.detail', ['order' => $order]))->name('user_orders.detail');
                Route::post('changePassword', UpdatePasswordController::class)->name('user_profile.changepassword');
                Route::view('locations', 'front.users.locations')->name('user_locations.list');
                Route::view('locations/add', 'front.users.locations.form')->name('user_location.add');
                Route::get('locations/{location}/edit', fn(Location $location) => view('front.users.locations.form', ['location' => $location]))->name('user_location.edit');
                Route::controller(UserController::class)->group(function (): void {
                    Route::get('locations/{location}/delete', 'locationDelete')->name('user_location.delete');
                    Route::post('locations/create', 'locationCreate')->name('user_location.create');
                    Route::post('locations/{location}/update', 'locationUpdate')->name('user_location.update');
                });
            },
        );

        Route::get('{cat}/promo/nos-promos', [
            ListingController::class,
            'promo',
        ])->name('promo');

        Route::get('{cat}/{type}/{slug}', [
            ListingController::class,
            'index',
        ])->name('list');

        require __DIR__ . '/auth.php';
    },
);
