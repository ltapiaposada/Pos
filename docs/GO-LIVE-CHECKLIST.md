# Checklist de salida a produccion

## Seguridad
- Rotar todas las credenciales expuestas historicamente.
- Confirmar `APP_DEBUG=false`.
- Confirmar `APP_ENV=production`.
- Confirmar `SEED_DEMO_DATA=false`.
- Confirmar `SESSION_SECURE_COOKIE=true`.
- Confirmar HTTPS activo.
- Confirmar secretos fuera del repositorio.
- Confirmar que no existen usuarios demo activos.

## Aplicacion
- Ejecutar `php artisan test`.
- Ejecutar `php artisan app:prepare-go-live` si la base viene de demo o sandbox.
- Ejecutar `php artisan migrate --seed --force`.
- Ejecutar `php artisan optimize:clear`.
- Ejecutar `php artisan config:cache`.
- Ejecutar `php artisan route:cache`.
- Ejecutar `php artisan view:cache`.
- Ejecutar `php artisan app:doctor`.

## Datos base
- Confirmar sucursal principal.
- Confirmar impuestos.
- Confirmar administrador inicial real.
- Confirmar moneda operativa de la empresa.
- Confirmar que no existen productos o contactos demo residuales.
- Confirmar productos visibles en ecommerce si aplica.
- Confirmar caja y flujo POS.

## Ecommerce
- Confirmar texto comercial del checkout.
- Confirmar metodos activos: `transfer`, `qr`, `contraentrega`.
- Confirmar QR configurado si se ofrece ese metodo.
- Confirmar costo de envio configurado por empresa.
- Confirmar cupones configurados por empresa o deshabilitados.
- Confirmar quien valida pagos manuales.

## Operacion
- Confirmar que `docker-compose.yml` o el pipeline usan credenciales reales.
- Confirmar estrategia de backup.
- Confirmar correo saliente.
- Confirmar almacenamiento de imagenes.
- Confirmar monitoreo/logs basicos.
- Confirmar responsable de hosting, deploy y restauracion.
- Confirmar procedimiento de cambio de contrasena inicial.

## Validacion final
- Crear una venta POS.
- Crear una compra.
- Crear un pedido ecommerce.
- Confirmar un pedido ecommerce desde admin.
- Registrar una factura/venta desde ecommerce admin.
