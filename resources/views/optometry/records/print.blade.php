<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historia clinica #{{ $record->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 24px; }
        .page { max-width: 900px; margin: 0 auto; }
        .header, .section { margin-bottom: 18px; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; font-size: 14px; }
        .card { border: 1px solid #d4d4d8; border-radius: 12px; padding: 14px; }
        .title { font-size: 24px; margin: 0 0 8px; }
        .print-btn { border: none; border-radius: 8px; padding: 10px 14px; background: #2563eb; color: white; font-weight: 700; cursor: pointer; }
        .muted { color: #52525b; }
        .sign { margin-top: 42px; border-top: 1px solid #111; padding-top: 8px; width: 280px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1 class="title">Historia clinica optometrica</h1>
            <div class="meta">
                <div><strong>Paciente:</strong> {{ $record->customer?->name }}</div>
                <div><strong>Documento:</strong> {{ $record->customer?->document ?? '-' }}</div>
                <div><strong>Fecha:</strong> {{ $record->examined_at->format('d/m/Y H:i') }}</div>
                <div><strong>Profesional:</strong> {{ $record->professional_name ?: ($record->createdBy?->name ?? '-') }}</div>
            </div>
        </div>

        <div class="section card"><strong>Motivo de consulta</strong><p class="muted">{{ $record->reason_for_consultation }}</p></div>
        <div class="section card"><strong>Antecedentes medicos</strong><p class="muted">{{ $record->medical_history ?: 'Sin registro.' }}</p></div>
        <div class="section card"><strong>Antecedentes oculares</strong><p class="muted">{{ $record->ocular_history ?: 'Sin registro.' }}</p></div>
        <div class="section card"><strong>Examen</strong><p class="muted">{{ $record->examination ?: 'Sin registro.' }}</p></div>
        <div class="section card"><strong>Diagnostico</strong><p class="muted">{{ $record->diagnosis ?: 'Sin registro.' }}</p></div>
        <div class="section card"><strong>Conducta o plan</strong><p class="muted">{{ $record->treatment_plan ?: 'Sin registro.' }}</p></div>
        <div class="section card"><strong>Observaciones</strong><p class="muted">{{ $record->observations ?: 'Sin registro.' }}</p></div>

        <div class="sign">
            {{ $record->professional_name ?: ($record->createdBy?->name ?? 'Profesional') }}<br>
            <span class="muted">{{ $record->professional_license ?: 'Sin registro profesional' }}</span>
        </div>

        <div class="no-print" style="margin-top: 20px;">
            <button class="print-btn" onclick="window.print()">Imprimir</button>
        </div>
    </div>
</body>
</html>
