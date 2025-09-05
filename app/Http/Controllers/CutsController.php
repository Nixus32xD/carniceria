<?php

namespace App\Http\Controllers;

use App\Models\Cut;
use Illuminate\Http\Request;

class CutsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255|unique:cuts,name',
            'category_id' => 'required|exists:categories,id',
        ]);

        $cut = Cut::create($validate);

        return response()->json([
            'message' => 'Corte creado exitosamente',
            'cut' => $cut
        ], 201); // ✅ 201 es correcto en creación
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255|unique:cuts,name,' . $id,
            'category_id' => 'required|exists:categories,id',
        ]);

        $cut = Cut::find($id);

        if (!$cut) {
            return response()->json(['message' => 'Corte no encontrado'], 404);
        }

        $cut->update($validate);

        return response()->json([
            'message' => 'Corte actualizado exitosamente',
            'cut' => $cut
        ], 200); // ✅ update devuelve 200
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cut = Cut::find($id);

        if ($cut) {
            $cut->delete();
            return response()->json([
                "message" => "Corte eliminado con éxito"
            ], 200);
        }

        return response()->json([
            "message" => "Corte no encontrado"
        ], 404);
    }
}
