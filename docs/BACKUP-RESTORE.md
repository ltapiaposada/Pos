# Backup y restauracion

## Base de datos
El proyecto usa MySQL 8+ como base de datos oficial.

## Backup manual
Script disponible:
- `scripts/backup-db.ps1`
- `scripts/restore-db.ps1`

Uso recomendado:
1. Confirmar que `.env` apunta a la base correcta.
2. Ejecutar el script con las credenciales del entorno.
3. Guardar el archivo resultante en almacenamiento externo.

Ejemplos:
- Backup: `powershell -ExecutionPolicy Bypass -File scripts/backup-db.ps1`
- Restore: `powershell -ExecutionPolicy Bypass -File scripts/restore-db.ps1 -InputFile backups/pos-20260531-220000.sql`

## Politica sugerida
- Backup diario de base de datos.
- Retencion minima de 7 dias.
- Retencion semanal de 4 semanas.
- Retencion mensual de 3 a 6 meses.

## Archivos a respaldar
- Base de datos MySQL
- Archivos subidos por usuarios
- Configuracion segura del entorno fuera del repositorio

## Restauracion
1. Crear una base de datos vacia.
2. Restaurar el dump MySQL con `scripts/restore-db.ps1` o con tu herramienta operativa.
3. Recuperar archivos de storage si aplica.
4. Verificar:
   - login de administrador
   - productos
   - inventario
   - ventas recientes
   - pedidos ecommerce

## Prueba de restauracion
Se recomienda hacer una restauracion de prueba al menos una vez al mes.

## Validacion post-restauracion
- Ejecutar `php artisan app:doctor`
- Ejecutar `php artisan test` en un entorno de validacion si aplica
- Verificar acceso al panel, POS y ecommerce

## Responsabilidad operativa
Antes de vender el producto, define si los backups seran:
- administrados por el cliente
- administrados por tu equipo
- administrados por el hosting

Eso debe quedar escrito en contrato o propuesta.
