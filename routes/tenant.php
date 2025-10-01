<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\HomepageController;
use App\Http\Controllers\Tenant\OrderController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Manage\TenantAssetController;
use App\Http\Controllers\Tenant\StandingOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Tenant homepage
    Route::get('/home', [HomepageController::class, 'index'])->name('home');

    // Product routes
    Route::resource('products', ProductController::class)->only(['show']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart/clear/{cart}', [CartController::class, 'clearCart'])->name('cart.clear');

    // Checkout and order routes
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/confirmation/{order}', [OrderController::class, 'confirmation'])->name('orders.confirmation');


    Route::get('tenant-asset/{path}', TenantAssetController::class)
        ->where('path', '.*')
        ->name('tenant.asset');

    // Templates (auth required)
    Route::middleware('auth')->group(function () {
        \App\Http\Controllers\Tenant\OrderTemplateController::class;
        Route::get('/templates', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'show'])->name('templates.show');
        Route::post('/templates/{template}/items', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'addItem'])->name('templates.items.add');
        Route::delete('/templates/{template}/items/{item}', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'removeItem'])->name('templates.items.remove');
        Route::post('/templates/{template}/apply', [\App\Http\Controllers\Tenant\OrderTemplateController::class, 'applyToCart'])->name('templates.apply');
    });

    // Include tenant-specific auth routes
    require __DIR__.'/tenant/auth.php';

    // Tenant dashboard and other protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('tenant/Dashboard', [
                'tenantId' => tenant('id'),
                'tenantName' => tenant('name')
            ]);
        })->name('dashboard');
        require __DIR__.'/tenant/admin.php';

        // Standing orders
        Route::get('/standing-orders', [StandingOrderController::class, 'index'])->name('standing-orders.index');
        Route::post('/standing-orders', [StandingOrderController::class, 'store'])->name('standing-orders.store');
        Route::post('/standing-orders/{standingOrder}/run', [StandingOrderController::class, 'run'])->name('standing-orders.run');
    });
});
