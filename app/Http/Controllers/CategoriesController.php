<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validate = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        // Crear la categoría
        $category = \App\Models\Category::create($validate);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'category' => $category
        ], 201);
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
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }
        // Actualizar la categoría
        $category->update($validate);

        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'category' => $category
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return response()->json([
                "message" => "Categoria eliminada con exito"
            ]);
        } else {
            return response()->json([
                "message" => "Categoria no encontrado"
            ], 404);
        }
    }
}
