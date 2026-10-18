# Integración entre aplicaciones

`administracion` es la fuente de verdad de las compañías (tenants) y entitlements.
`pos-operativo` es la fuente de verdad de catálogo, inventario y ventas.
`ecommerce` conserva proyecciones de catálogo y pedidos. Aunque POS y e-commerce usan la misma base de datos física en hosting compartido, e-commerce no consulta ni modifica tablas operativas del POS: usa la API interna.

El único identificador de empresa basado en dominio es `ecommerce.storefronts.domain`: un host como `empresa.tudominio.com` se resuelve a su tenant. POS y administración usan hosts fijos y el tenant llega por UUID, sesión o API; no deben aceptar subdominios de clientes.

La tabla `administracion.companies` representa la empresa cliente y guarda su información comercial y administrativa. E-commerce consulta esa base y almacena solo `company_id` como referencia lógica. POS guarda también solo `company_id` en sus tablas operativas; no conserva una tabla `companies` ni existen llaves foráneas entre aplicaciones. `tenant_uuid` se conserva en Administración como identificador público de las APIs.

Las APIs internas se autentican con `X-Internal-Token`; las tres aplicaciones deben usar el mismo valor secreto. Los eventos se identifican por UUID y se deduplican. Se entregan dentro de la solicitud que los origina y, si hay un fallo temporal, quedan registrados en el outbox para reintento manual. Keycloak puede añadirse posteriormente como SSO externo si se dispone de un servidor compatible.
