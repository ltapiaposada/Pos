<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance general</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1 { margin: 0 0 8px; font-size: 20px; }
        p { margin: 0 0 16px; color: #4b5563; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; font-size: 12px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .total { font-weight: 700; }
    </style>
</head>
<body>
    <h1>Balance general</h1>
    <p>Corte a: {{ $asOf }}</p>

    <h3>Activos</h3>
    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th class="right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assetRows as $row)
                <tr>
                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                    <td class="right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total activos</td>
                <td class="right">{{ number_format($totalAssets, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Pasivos</h3>
    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th class="right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($liabilityRows as $row)
                <tr>
                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                    <td class="right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total pasivos</td>
                <td class="right">{{ number_format($totalLiabilities, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Patrimonio</h3>
    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th class="right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($equityRows as $row)
                <tr>
                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                    <td class="right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total patrimonio</td>
                <td class="right">{{ number_format($totalEquity, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Pasivo + patrimonio</td>
                <td class="right">{{ number_format($totalLiabilitiesAndEquity, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Diferencia</td>
                <td class="right">{{ number_format($difference, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
