<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orden medica #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 24px; }
        .page { max-width: 900px; margin: 0 auto; }
        .section { border: 1px solid #d4d4d8; border-radius: 12px; padding: 14px; margin-bottom: 18px; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; font-size: 14px; margin-bottom: 16px; }
        .title { font-size: 24px; margin: 0 0 10px; }
        .print-btn { border: none; border-radius: 8px; padding: 10px 14px; background: #2563eb; color: white; font-weight: 700; cursor: pointer; }
        .sign { margin-top: 42px; border-top: 1px solid #111; padding-top: 8px; width: 280px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="page">
        <h1 class="title">Orden medica optometrica</h1>
        <div class="meta">
            <div><strong>Paciente:</strong> {{ $order->customer?->name }}</div>
            <div><strong>Documento:</strong> {{ $order->customer?->document ?? '-' }}</div>
            <div><strong>Fecha:</strong> {{ $order->ordered_at->format('d/m/Y H:i') }}</div>
            <div><strong>Profesional:</strong> {{ $order->professional_name ?: ($order->createdBy?->name ?? '-') }}</div>
        </div>

        <div class="section"><strong>Formula o indicaciones</strong><p>{{ $order->prescription_details }}</p></div>
        <div class="section"><strong>Instrucciones de uso</strong><p>{{ $order->usage_instructions ?: 'Sin registro.' }}</p></div>
        <div class="section"><strong>Observaciones</strong><p>{{ $order->observations ?: 'Sin registro.' }}</p></div>

        <div class="sign">
            {{ $order->professional_name ?: ($order->createdBy?->name ?? 'Profesional') }}<br>
            {{ $order->professional_license ?: 'Sin registro profesional' }}
        </div>

        <div class="no-print" style="margin-top: 20px;">
            <button class="print-btn" onclick="window.print()">Imprimir</button>
        </div>
    </div>
</body>
</html>
