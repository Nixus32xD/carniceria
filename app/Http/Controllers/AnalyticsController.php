<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // ===============================
        // 🔹 1. Métricas Generales (totales globales)
        // ===============================
        $totalRevenue = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->sum('sale_items.subtotal');

        $totalOrders = Sale::count();
        $uniqueCustomers = Sale::distinct('customer_id')->count('customer_id');
        $avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // ===============================
        // 🔹 1.b Calcular cambios dinámicos (últimos 30 días vs 30 días previos)
        // ===============================
        $startCurrent = Carbon::now()->subDays(30);
        $endCurrent = Carbon::now();

        $startPrev = $startCurrent->copy()->subDays(30);
        $endPrev = $startCurrent;

        // Valores actuales
        $currentRevenue = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$startCurrent, $endCurrent])
            ->sum('sale_items.subtotal');

        $currentOrders = Sale::whereBetween('created_at', [$startCurrent, $endCurrent])->count();
        $currentCustomers = Sale::whereBetween('created_at', [$startCurrent, $endCurrent])
            ->distinct('customer_id')
            ->count('customer_id');
        $currentAvgOrder = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

        // Valores anteriores
        $prevRevenue = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$startPrev, $endPrev])
            ->sum('sale_items.subtotal');

        $prevOrders = Sale::whereBetween('created_at', [$startPrev, $endPrev])->count();
        $prevCustomers = Sale::whereBetween('created_at', [$startPrev, $endPrev])
            ->distinct('customer_id')
            ->count('customer_id');
        $prevAvgOrder = $prevOrders > 0 ? $prevRevenue / $prevOrders : 0;

        // Helper para calcular % cambio
        $calcChange = function ($current, $previous) {
            if ($previous > 0) {
                $diff = round((($current - $previous) / $previous) * 100, 1);
                return ($diff >= 0 ? '+' : '') . $diff . '%';
            }
            return '+0%';
        };

        // ===============================
        // 🔹 2. Top Productos
        // ===============================
        $topProducts = SaleItem::selectRaw('product_name as name, SUM(quantity) as sales, SUM(subtotal) as revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sales')
            ->limit(5)
            ->get();

        // ===============================
        // 🔹 3. Comparaciones por período
        // ===============================
        $periods = [
            'last_week' => [Carbon::now()->subWeek(), Carbon::now()],
            'last_month' => [Carbon::now()->subMonth(), Carbon::now()],
            'last_quarter' => [Carbon::now()->subMonths(3), Carbon::now()],
        ];

        $comparisons = [];

        foreach ($periods as $key => [$start, $end]) {
            $currentRevenue = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereBetween('sales.created_at', [$start, $end])
                ->sum('sale_items.subtotal');

            $currentOrders = Sale::whereBetween('created_at', [$start, $end])->count();
            $currentAvgOrder = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

            $prevStart = $start->copy()->sub($end->diff($start));
            $prevEnd = $start;

            $prevRevenue = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->whereBetween('sales.created_at', [$prevStart, $prevEnd])
                ->sum(DB::raw('sale_items.quantity * sale_items.unit_price'));

            $revenueChange = $prevRevenue > 0
                ? round((($currentRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
                : 0;

            $comparisons[$key] = [
                'revenue' => $currentRevenue,
                'orders' => $currentOrders,
                'avgOrder' => $currentAvgOrder,
                'revenueChange' => $revenueChange,
            ];
        }

        // ===============================
        // 🔹 4. Datos para Gráficos de Tendencia
        // ===============================
        $trends = [
            'daily' => $this->getDailySalesTrend(),
            'monthly' => $this->getMonthlySalesTrend(),
        ];

        // ===============================
        // 🔹 5. Enviar a Vue
        // ===============================
        return Inertia::render('Analytics', [
            'analyticsData' => [
                'metrics' => [
                    'revenue' => [
                        'label' => 'Ingresos',
                        'value' => $totalRevenue,
                        'change' => $calcChange($currentRevenue, $prevRevenue),
                        'color' => 'red'
                    ],
                    'orders' => [
                        'label' => 'Pedidos',
                        'value' => $totalOrders,
                        'change' => $calcChange($currentOrders, $prevOrders),
                        'color' => 'red'
                    ],
                    'customers' => [
                        'label' => 'Clientes',
                        'value' => $uniqueCustomers,
                        'change' => $calcChange($currentCustomers, $prevCustomers),
                        'color' => 'red'
                    ],
                    'avgOrder' => [
                        'label' => 'Pedido Promedio',
                        'value' => round($avgOrder, 2),
                        'change' => $calcChange($currentAvgOrder, $prevAvgOrder),
                        'color' => 'red'
                    ]
                ],
                'topProducts' => $topProducts,
            ],
            'comparisons' => $comparisons,
            'trends' => $trends,
        ]);
    }

    private function getDailySalesTrend()
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $revenues = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select(
                DB::raw('DATE(sales.created_at) as date'),
                DB::raw('SUM(sale_items.subtotal) as revenue')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $orders = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as orders')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('orders', 'date');

        // combinar resultados
        $dates = collect(array_unique(array_merge($revenues->keys()->toArray(), $orders->keys()->toArray())));
        return $dates->map(function ($date) use ($revenues, $orders) {
            return [
                'date' => $date,
                'revenue' => (float) ($revenues[$date] ?? 0),
                'orders' => (int) ($orders[$date] ?? 0),
            ];
        })->values();
    }


    private function getMonthlySalesTrend()
    {
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        $revenues = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select(
                DB::raw('YEAR(sales.created_at) as year'),
                DB::raw('MONTH(sales.created_at) as month'),
                DB::raw('SUM(sale_items.subtotal) as revenue')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                return [$key => $item->revenue];
            });

        $orders = Sale::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(id) as orders')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                return [$key => $item->orders];
            });

        $periods = collect(array_unique(array_merge($revenues->keys()->toArray(), $orders->keys()->toArray())));

        return $periods->map(function ($period) use ($revenues, $orders) {
            return [
                'period' => $period,
                'revenue' => (float) ($revenues[$period] ?? 0),
                'orders' => (int) ($orders[$period] ?? 0),
            ];
        })->values();
    }
}
