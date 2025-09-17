<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === 1. Ventas diarias (últimos 7 días) ===
        $dailySales = Sale::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyLabels = $dailySales->map(fn($s) => Carbon::parse($s->date)->locale('es')->shortDayName);
        $dailyValues = $dailySales->map(fn($s) => $s->total);

        // === 2. Ventas mensuales (últimos 6 meses) ===
        $monthlySales = Sale::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as total')
            ->where('created_at', '>=', Carbon::now()->subMonths(5))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyLabels = $monthlySales->map(fn($s) => Carbon::createFromFormat('Y-m', $s->month)->locale('es')->shortMonthName);
        $monthlyValues = $monthlySales->map(fn($s) => $s->total);
        $uniqueCustomers = Sale::distinct('customer_id')->count('customer_id');

        // === 3. Productos vendidos ===
        $productSales = Product::withSum('saleItems as sales', 'quantity')
            ->orderByDesc('sales')
            ->get(['id', 'name']);

        $productsData = $productSales->map(fn($p) => [
            'name' => $p->name,
            'sales' => $p->sales ?? 0,
        ]);

        // === 4. Ingresos totales ===
        $revenueData = [
            'daily'   => $dailyValues->sum(),
            'monthly' => $monthlyValues->sum(),
        ];

        // === 5. Pedidos recientes ===
        $recentOrders = Sale::latest()->take(5)->with('items')->get()->map(function ($sale) {
            return [
                'id'       => $sale->id,
                'customer' => Customer::find($sale->customer_id)->name ?? 'Cliente General',
                'total'    => $sale->total,
                'items'    => $sale->items->pluck('product_name')->join(', '),
            ];
        });

        return Inertia::render('Dashboard', [
            'salesData' => [
                'daily' => [
                    'labels' => $dailyLabels,
                    'values' => $dailyValues,
                ],
                'monthly' => [
                    'labels' => $monthlyLabels,
                    'values' => $monthlyValues,
                ],
                'recentOrders' => $recentOrders,
            ],
            'productsData' => $productsData,
            'revenueData'  => $revenueData,
            'topProducts'  => $productsData->take(5), // opcional
            'uniqueCustomers' => $uniqueCustomers,
        ]);
    }
}
