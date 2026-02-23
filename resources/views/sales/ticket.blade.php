<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ticket #{{ $sale->sale_number }}</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
            .ticket { max-width: 320px; margin: 0 auto; }
            .center { text-align: center; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 4px 0; }
            .totals td { font-weight: bold; }
            .print-btn {
                border: none;
                border-radius: 8px;
                padding: 8px 12px;
                background: #2563eb;
                color: #fff;
                font-weight: 700;
                cursor: pointer;
                width: 100%;
                transition: background .15s ease;
            }
            .print-btn:hover { background: #1d4ed8; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        @php
            $business = \App\Models\Setting::getValue('business', []);
            $logoUrl = $business['logo_url'] ?? null;
        @endphp
        <div class="ticket">
            <div class="center">
                <div style="margin-bottom: 6px;">
                    <img
                        src="{{ $logoUrl ?: asset('images/product-placeholder.svg') }}"
                        alt="{{ $logoUrl ? 'Logo' : 'Sin logo' }}"
                        style="max-height: 42px; max-width: 140px; object-fit: contain;"
                    >
                </div>
                <h3>{{ $sale->branch->name }}</h3>
                <div>Ticket #{{ $sale->sale_number }}</div>
                <div>{{ $sale->sold_at->format('d/m/Y H:i') }}</div>
            </div>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="center">${{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Descuento</td>
                    <td class="center">${{ number_format($sale->discount_total, 2) }}</td>
                </tr>
                <tr>
                    <td>Impuesto</td>
                    <td class="center">${{ number_format($sale->tax_total, 2) }}</td>
                </tr>
                <tr class="totals">
                    <td>Total</td>
                    <td class="center">${{ number_format($sale->total, 2) }}</td>
                </tr>
            </table>
            <hr>
            <div class="center">
                Gracias por su compra
            </div>
            <div class="center no-print" style="margin-top: 10px;">
                <button class="print-btn" onclick="window.print()">Imprimir</button>
            </div>
        </div>
        <script>
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ type: 'pos-sale-completed' }, window.location.origin);
            }
        </script>
    </body>
</html>
