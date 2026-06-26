# Despliegue base

## Docker Compose local o servidor simple

1. Configura credenciales reales en `docker-compose.yml` o en un archivo `.env` del servidor.
2. Ajusta `POS_INITIAL_ADMIN_*` para crear el primer administrador.
3. Verifica que `SEED_DEMO_DATA=false`.
4. Levanta los servicios:

```bash
docker compose up -d --build
```

5. Abre la aplicacion en `http://localhost:8080`.
6. Si partes de una base con datos demo, ejecuta `php artisan app:prepare-go-live` con los datos reales del cliente.

## Que hace el contenedor

- instala dependencias PHP de produccion
- publica Apache sobre `/public`
- genera `APP_KEY`
- ejecuta migraciones y seed inicial
- deja la instalacion sin usuarios demo cuando `SEED_DEMO_DATA=false`

## Recomendaciones antes de salir a produccion

- reemplazar contrasenas de ejemplo del `docker-compose.yml`
- mover variables sensibles a secretos del servidor
- configurar dominio real en la empresa inicial
- revisar almacenamiento de archivos y backups
- compilar assets front si el flujo de entrega no usa artefactos ya construidos
