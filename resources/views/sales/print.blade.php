<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Venta #{{ $sale->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; font-size: 14px; }
        h1, h2, h3 { margin: 0; }
        .header, .footer { text-align: center; }
        .header { margin-bottom: 20px; }
        .footer { margin-top: 40px; font-size: 12px; color: #777; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
        .details { margin-top: 20px; }
        .details p { margin: 2px 0; }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Encabezado -->
    <div class="header">
        <h1>CARNICERÍA</h1>
        <h3>Factura de Venta</h3>
        <p><strong>N° de Venta:</strong> {{ $sale->id }}</p>
    </div>

    <!-- Datos del Cliente -->
    <div class="details">
        <p><strong>Cliente:</strong> {{ $sale->customer->name ?? 'Cliente General' }}</p>
        <p><strong>Teléfono:</strong> {{ $sale->customer->phone ?? '-' }}</p>
        <p><strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Método de Pago:</strong> {{ ucfirst($sale->payment_method) }}</p>
    </div>

    <!-- Tabla de productos -->
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad (kg)</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <p class="total">TOTAL: ${{ number_format($sale->total, 2) }}</p>

    <!-- Notas -->
    @if($sale->notes)
        <p><strong>Notas:</strong> {{ $sale->notes }}</p>
    @endif

    <!-- Pie de página -->
    <div class="footer">
        <p>Gracias por su compra.</p>
    </div>

</body>
</html>
