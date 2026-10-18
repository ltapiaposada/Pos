<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de resultados</title>
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
    <h1>Estado de resultados</h1>
    <p>Periodo: {{ $from ?: 'Inicio' }} a {{ $to ?: now()->toDateString() }}</p>

    <h3>Ingresos</h3>
    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incomeRows as $row)
                <tr>
                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                    <td class="right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total ingresos</td>
                <td class="right">{{ number_format($totalIncome, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Gastos</h3>
    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenseRows as $row)
                <tr>
                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                    <td class="right">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total gastos</td>
                <td class="right">{{ number_format($totalExpense, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Utilidad neta</td>
                <td class="right">{{ number_format($netIncome, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
