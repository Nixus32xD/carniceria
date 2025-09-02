<?php

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index/inicio', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name("home");

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/products',[ProductsController::class, 'index'] )->name('products.index');
    Route::post('/dashboard/products',[ProductsController::class, 'store'] )->name('products.store');
    Route::put('/dashboard/products/{id}/update',[ProductsController::class, 'update'] )->name('products.update');
    Route::delete('/dashboard/product/{id}/delete', [ProductsController::class, 'destroy'] )->name('products.destroy');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/sales', function () {
    $products = Product::all();
    return Inertia::render('Sales', [
        "products" => $products,
        "customers" => ["", ""]
    ]);
})->middleware(['auth', 'verified'])->name('sales.index');

Route::get('/dashboard/analytics', function () {
    return Inertia::render('Analytics');
})->middleware(['auth', 'verified'])->name('analytics.index');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
