# Importación SQL para hosting

Estos scripts son para phpMyAdmin o MySQL/MariaDB. Ejecutarlos una sola vez y en el orden indicado. No contienen sentencias `DROP` ni borran tablas existentes.

1. Importar el respaldo o esquema actual del POS en la base `usuario_pos_core`.
2. Seleccionar `usuario_pos_core` e importar `01_pos_core_ecommerce.sql`.
3. Ejecutar `00_crear_base_administracion.sql`.
4. Seleccionar `administracion` e importar `02_administracion.sql`.

Después de importar los archivos, configurar los `.env` y ejecutar únicamente los comandos de caché de Laravel:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No ejecutar `php artisan migrate` inmediatamente después de estos imports: los scripts ya registran las migraciones incluidas. En una actualización futura, usar `php artisan migrate --force` solo cuando haya nuevas migraciones de esa versión.

`01_pos_core_ecommerce.sql` requiere que el POS exista porque agrega campos a `companies`, `customers` y `sales`. No debe importarse sobre una base vacía.

Si por error se importó `02_administracion.sql` en la base `saturnext`, ejecutar primero las migraciones de Administración y E-commerce y luego importar `03_eliminar_administracion_de_saturnext.sql` estando seleccionada esa base. El script elimina las tablas administrativas erróneas; e-commerce ya usa `ecommerce_*` y consulta la compañía en la base `administracion`. La tabla `companies` se mueve mediante la migración del POS, no con ese script.
