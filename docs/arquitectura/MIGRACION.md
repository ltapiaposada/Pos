# Migración y arranque local

## Arranque local

1. Copia `.env.example` a `.env` en cada aplicación, configura MySQL y asigna el mismo `INTERNAL_API_TOKEN` local.
2. Ejecuta las migraciones por separado y en este orden:

```bash
cd administracion && php artisan migrate
cd ../ecommerce && php artisan migrate
cd ../pos-operativo && php artisan migrate
```

3. En tres terminales distintas, inicia las aplicaciones normales:

```bash
cd pos-operativo && php artisan serve --port=8000
cd ecommerce && php artisan serve --port=8001
cd administracion && php artisan serve --port=8002
```

El POS se abre en `http://localhost:8000`, e-commerce en `http://localhost:8001` y administración en `http://localhost:8002`. POS y e-commerce comparten únicamente la base física `saturnext`, pero no tablas: e-commerce usa el registro `ecommerce_migrations` y sus tablas `ecommerce_*`.

## Corte de datos

1. Crear un respaldo de las bases `saturnext` y `administracion`.
2. Migrar Administración, E-commerce y POS, exactamente en ese orden. La migración de POS copia las compañías conservando su `company_id`, elimina las llaves foráneas que apuntaban a `companies` y después elimina esa tabla local.
3. Si se importaron por error tablas administrativas en `saturnext`, ejecutar el SQL de limpieza:

```bash
mysql -h 127.0.0.1 -u root saturnext < database/sql/03_eliminar_administracion_de_saturnext.sql
```
4. Aprovisionar cada compañía en e-commerce desde administración.
5. Sincronizar catálogo y disponibilidad desde las APIs del POS antes de cambiar un dominio público.
6. Validar pedidos históricos y totales por compañía; los carritos no se migran.
7. Retirar las rutas públicas antiguas del POS únicamente después de que el dominio haya sido validado en e-commerce.

La migración conserva las tablas operativas y sus `company_id`; elimina solamente el maestro duplicado `saturnext.companies` y las llaves foráneas que lo apuntaban. El maestro queda exclusivamente en `administracion.companies`.
