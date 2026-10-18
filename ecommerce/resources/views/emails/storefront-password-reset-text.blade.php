Hola{{ !empty($customer['name']) ? ' '.$customer['name'] : '' }},

Recibimos una solicitud para restablecer la contraseña de tu cuenta en {{ $storeName }}.

Crea una nueva contraseña desde este enlace:
{{ $url }}

El enlace vence en 60 minutos. Si no solicitaste este cambio, puedes ignorar este correo.
