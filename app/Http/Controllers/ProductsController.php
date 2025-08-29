<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();

        return inertia('Products/Products', [
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'isActive'    => 'boolean',
            'isOffer'     => 'boolean',
            'stock'       => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',

            // Si hay oferta, el descuento es obligatorio
            'discount'    => 'nullable|numeric|min:0|max:100',
            'offerPrice'  => 'nullable|numeric|min:0',
        ]);

        // 🔹 Reglas extra en backend (por seguridad)
        if ($validate['stock'] == 0) {
            $validate['isActive'] = false;
        } elseif (!isset($validate['isActive'])) {
            $validate['isActive'] = true;
        }

        if (!empty($validate['isOffer']) && !empty($validate['discount'])) {
            $validate['offerPrice'] = $validate['price'] - ($validate['price'] * ($validate['discount'] / 100));
        } else {
            $validate['offerPrice'] = null;
            $validate['discount']   = null;
        }

        $product = Product::create($validate);

        return response()->json([
            "message" => "Producto creado con exito",
            "product" => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'isActive'    => 'boolean',
            'isOffer'     => 'boolean',
            'stock'       => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',

            // Si hay oferta, el descuento es obligatorio
            'discount'    => 'nullable|numeric|min:0|max:100',
            'offerPrice'  => 'nullable|numeric|min:0',
        ]);

        // 🔹 Reglas extra en backend (por seguridad)
        // Lógica extra: si el stock es 0, se fuerza isActive = false
        if ($validate['stock'] == 0) {
            $validate['isActive'] = false;
        }

        // 🔹 Recalcular precio de oferta
        if (!empty($validate['isOffer']) && !empty($validate['discount'])) {
            $validate['offerPrice'] = $validate['price'] - ($validate['price'] * ($validate['discount'] / 100));
        } else {
            $validate['offerPrice'] = null;
            $validate['discount']   = null;
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $product->update($validate);

        return response()->json([
            'message' => 'Producto actualizado con éxito',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return response()->json([
                "message" => "Producto eliminado con exito"
            ]);
        } else {
            return response()->json([
                "message" => "Producto no encontrado"
            ], 404);
        }
    }
}
