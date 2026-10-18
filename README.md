# Plataforma POS

Este repositorio contiene tres aplicaciones Laravel independientes:

- `pos-operativo`: operación, inventario, ventas y contabilidad.
- `ecommerce`: tienda pública y pedidos web.
- `administracion`: empresas cliente, servicios y suscripciones.

## Diseño para hosting compartido

La plataforma usa dos bases de datos:

- `pos_core`: POS y e-commerce. Cada aplicación mantiene sus migraciones y sus tablas propias; e-commerce se comunica con el POS por API y no consulta sus tablas operativas.
- `administracion`: empresas, planes, servicios, pagos y aprovisionamiento.

No requiere workers ni procesos de cola. El aprovisionamiento se realiza durante la solicitud de administración, el checkout se registra directamente en POS y la tienda actualiza el catálogo al recibir una visita. Keycloak queda como integración opcional externa, no como requisito del hosting compartido.

Ejecuta la plataforma local con `docker compose -f docker-compose.local.yml up --build`.
Consulta la guía de despliegue en `docs/arquitectura/DESPLIEGUE-HOSTING-COMPARTIDO.md`.
