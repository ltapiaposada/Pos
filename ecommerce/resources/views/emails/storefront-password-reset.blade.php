<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablece tu contraseña</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f2;color:#15181d;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f5f2;">
        <tr><td align="center" style="padding:38px 18px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 28px rgba(18,51,41,.10);">
                <tr><td style="padding:30px 38px;background:#1e4a3d;color:#ffffff;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>
                        <td style="vertical-align:middle;padding-right:12px;">
                            <table role="presentation" cellspacing="3" cellpadding="0" border="0"><tr><td width="10" height="10" style="border-radius:3px;background:#ffffff;"></td><td width="10" height="10" style="border-radius:3px;background:#d9a441;"></td></tr><tr><td width="10" height="10" style="border-radius:3px;background:#c66b4d;"></td><td width="10" height="10" style="border-radius:3px;background:#96b4aa;"></td></tr></table>
                        </td>
                        <td style="font-size:14px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">{{ $storeName }}</td>
                    </tr></table>
                    <p style="margin:28px 0 0;color:#cfe0d8;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Acceso seguro</p>
                    <h1 style="margin:9px 0 0;font-family:Georgia,serif;font-size:34px;font-weight:normal;line-height:1.14;">Restablece tu contraseña</h1>
                </td></tr>
                <tr><td style="padding:38px;">
                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hola{{ !empty($customer['name']) ? ' '.$customer['name'] : '' }},</p>
                    <p style="margin:0 0 26px;color:#555f68;font-size:15px;line-height:1.65;">Recibimos una solicitud para crear una nueva contraseña para tu cuenta. Usa el botón para continuar de forma segura.</p>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td style="border-radius:7px;background:#1e4a3d;"><a href="{{ $url }}" style="display:inline-block;padding:14px 22px;border-radius:7px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Crear nueva contraseña</a></td></tr></table>
                    <p style="margin:28px 0 0;padding-top:22px;border-top:1px solid #e5e3dc;color:#68707a;font-size:13px;line-height:1.6;">Este enlace vence en <strong style="color:#303840;">60 minutos</strong>. Si no solicitaste este cambio, puedes ignorar este correo; tu contraseña actual seguirá protegida.</p>
                    <p style="margin:22px 0 0;color:#879099;font-size:12px;line-height:1.55;">Si el botón no abre, copia y pega este enlace en tu navegador:<br><a href="{{ $url }}" style="color:#1e4a3d;word-break:break-all;">{{ $url }}</a></p>
                </td></tr>
                <tr><td style="padding:22px 38px;background:#f7f6f2;border-top:1px solid #e9e6dd;color:#788089;font-size:12px;line-height:1.5;">Este es un mensaje automático de {{ $storeName }}. Por favor, no respondas a este correo.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
