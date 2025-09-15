@component('mail::message')
    # Alerta de Bajo Stock

    Los siguientes productos tienen poco stock:

    @foreach ($products as $product)
        - {{ $product->name }} → Stock: {{ $product->stock }}
    @endforeach

    Por favor, reabastecer lo antes posible.

    Gracias,
    Sistema de Carnicería
@endcomponent
