<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CutsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Models\Category;
use App\Models\Cut;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// Route::get('/', function () {
//     $products = Product::with(['category', 'cut'])->get(); // Cargar relaciones
//     $categories = Category::all();
//     $cuts = Cut::all();

//     return Inertia::render('Index/inicio', [
//         "products" => $products,
//         "categories" => $categories,
//         "cuts" => $cuts
//     ]);
// })->name("home");



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get("/" ,[DashboardController::class, 'index'])->name('home');

    Route::get('/dashboard/products', [ProductsController::class, 'index'])->name('products.index');
    Route::post('/dashboard/products', [ProductsController::class, 'store'])->name('products.store');
    Route::put('/dashboard/products/{id}/update', [ProductsController::class, 'update'])->name('products.update');
    Route::delete('/dashboard/product/{id}/delete', [ProductsController::class, 'destroy'])->name('products.destroy');

    Route::post('/dashboard/categories', [CategoriesController::class, 'store'])->name('categories.store');
    Route::put('/dashboard/categories/{id}', [CategoriesController::class, 'update'])->name('categories.update');
    Route::delete('/dashboard/categories/{id}/delete', [CategoriesController::class, 'destroy'])->name('categories.destroy');

    Route::post('/dashboard/cuts', [CutsController::class, 'store'])->name('cuts.store');
    Route::put('/dashboard/cuts/{id}', [CutsController::class, 'update'])->name('cuts.update');
    Route::delete('/dashboard/cuts/{id}/delete', [CutsController::class, 'destroy'])->name('cuts.destroy');

    Route::get('/dashboard/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/dashboard/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/dashboard/sales/{id}', [SalesController::class, 'show'])->name('sales.show');

    // Rutas para imprimir recibos
    Route::get('/dashboard/sales/{id}/print', [SalesController::class, 'print'])->name('sales.print');
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get("/dashboard" ,[DashboardController::class, 'index'])->name('dashboard');

});





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
