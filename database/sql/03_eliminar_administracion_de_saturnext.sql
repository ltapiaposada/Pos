-- Elimina las tablas administrativas creadas por error en saturnext y el tenant legado de e-commerce.
-- Destino: base saturnext.
-- Ejecutar DESPUÉS de la migración e-commerce 2026_10_18_000000.
-- La base correcta de Administración es `administracion`.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `provisioning_jobs`;
DROP TABLE IF EXISTS `tenant_domains`;
DROP TABLE IF EXISTS `entitlements`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `plans`;
DROP TABLE IF EXISTS `platform_services`;
DROP TABLE IF EXISTS `administracion_migrations`;
-- El tenant legado fue usado solamente por la extracción inicial de e-commerce.
-- Ya no es necesario después de la migración 2026_10_18_020000.
DROP TABLE IF EXISTS `tenants`;

SET FOREIGN_KEY_CHECKS = 1;
