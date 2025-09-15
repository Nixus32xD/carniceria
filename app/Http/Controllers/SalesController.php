<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Pest\ArchPresets\Custom;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['customer'])->orderBy('created_at', 'desc')->get();

        $products = Product::all();
        $customers = Customer::all();

        return Inertia::render('Sales', [
            "sales" => $sales,
            "products" => $products,
            "customers" => $customers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'payment_method' => 'required|string|in:efectivo,tarjeta,transferencia',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Buscar o crear cliente
            $customer = Customer::firstOrCreate(
                ['phone' => $request->customer_phone ?? null, 'name' => $request->customer_name],
                [
                    'name' => $request->customer_name ?? 'Cliente General',
                    'phone' => $request->customer_phone,
                    'address' => ''
                ]
            );

            // Crear venta
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? ''
            ]);

            // Crear items de venta
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal']
                ]);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$product->name}");
                }
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();  // ← FUERA del foreach

            return response()->json([
                'success' => true,
                'message' => 'Venta creada con éxito.',
                'customer' => $customer,
                'sale' => $sale->load('customer', 'items')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $sale = Sale::with([
                'customer:id,name,phone,email',
                'items:id,sale_id,product_id,product_name,quantity,unit_price,subtotal',
                // 'items.product:id,name,unit' // Relación opcional, comenta si falla
            ])->find($id);

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'sale' => $sale,
                'message' => 'Venta obtenida exitosamente'
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la venta' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    public function print(string $id)
    {
        $sale = Sale::with(['customer', 'items'])->findOrFail($id);

        return view('sales.print', compact('sale'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
