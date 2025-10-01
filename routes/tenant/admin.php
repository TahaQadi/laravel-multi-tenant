<?php

use App\Http\Controllers\Tenant\Manage\ProductController;
use App\Http\Controllers\Tenant\Manage\CategoryController;
use App\Http\Controllers\Tenant\Manage\CustomerController;
use App\Http\Controllers\Tenant\Manage\OrderController;
use App\Http\Controllers\Tenant\Manage\ContractController;
use App\Http\Controllers\Tenant\Manage\ApprovalController;
use App\Http\Controllers\Tenant\Manage\StoreStatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/store/stats', [StoreStatsController::class, 'getStats'])->name('store.stats');

    Route::group(['prefix'=> 'manage'], function() {
        // Product Routes
        Route::group(['prefix' => 'product'], function () {
            Route::get('/', [ProductController::class, 'index'])->name('product.index');
            Route::get('/create', [ProductController::class, 'create'])->name('product.create');
            Route::post('/', [ProductController::class, 'store'])->name('product.store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('product.show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
            Route::post('/{product}/edit', [ProductController::class, 'update'])->name('product.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
        });

        // Category Routes
        Route::group(['prefix' => 'category'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('category.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('category.create');
            Route::post('/', [CategoryController::class, 'store'])->name('category.store');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('category.show');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
            Route::post('/{category}/edit', [CategoryController::class, 'update'])->name('category.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
        });

        // Order Routes
        Route::group(['prefix' => 'order'], function () {
            Route::get('/dashboard-stats', [OrderController::class, 'getDashboardStats'])->name('order.dashboard-stats');
            Route::get('/', [OrderController::class, 'index'])->name('order.index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('order.show');
            Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->name('order.update-status');
            Route::post('/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('order.update-payment-status');
        });

        // Customer Routes
        Route::group(['prefix' => 'customer'], function () {
            Route::get('/', [CustomerController::class, 'index'])->name('customer.index');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('customer.show');
        });

        // Contract Routes
        Route::group(['prefix' => 'contract'], function () {
            Route::get('/', [ContractController::class, 'index'])->name('manage.contract.index');
            Route::get('/create', [ContractController::class, 'create'])->name('manage.contract.create');
            Route::post('/', [ContractController::class, 'store'])->name('manage.contract.store');
            Route::get('/{contract}', [ContractController::class, 'show'])->name('manage.contract.show');
            Route::post('/{contract}/upload-items', [ContractController::class, 'uploadItems'])->name('manage.contract.upload-items');
        });

        // Approval Routes
        Route::group(['prefix' => 'approvals'], function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('manage.approvals.index');
            Route::post('/{approval}/decide', [ApprovalController::class, 'decide'])->name('manage.approvals.decide');
        });
    });
});
