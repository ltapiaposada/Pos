-- Base compartida: POS + e-commerce
-- Destino: la base de datos existente del POS, por ejemplo usuario_pos_core.
-- Requisito: las tablas actuales del POS ya deben existir.
-- Ejecutar UNA sola vez desde phpMyAdmin o el cliente MySQL. No elimina datos.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `migrations_migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campos de integración añadidos al POS. Las compañías viven exclusivamente
-- en la base administracion; las tablas operativas conservan solo company_id.
ALTER TABLE `customers`
    ADD COLUMN `external_customer_uuid` char(36) NULL AFTER `company_id`,
    ADD KEY `customers_company_external_customer_index` (`company_id`, `external_customer_uuid`);

ALTER TABLE `sales`
    ADD COLUMN `external_order_uuid` char(36) NULL AFTER `company_id`,
    ADD UNIQUE KEY `sales_external_order_uuid_unique` (`external_order_uuid`);

CREATE TABLE IF NOT EXISTS `company_entitlements` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `service` varchar(50) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'enabled',
    `expires_at` timestamp NULL DEFAULT NULL,
    `payload` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `company_entitlements_company_id_service_unique` (`company_id`, `service`),
    KEY `company_entitlements_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `integration_outbox_events` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `event_uuid` char(36) NOT NULL,
    `event_type` varchar(100) NOT NULL,
    `payload` json NOT NULL,
    `published_at` timestamp NULL DEFAULT NULL,
    `attempts` int unsigned NOT NULL DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `integration_outbox_events_event_uuid_unique` (`event_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de migraciones propio de e-commerce para no interferir con POS.
CREATE TABLE IF NOT EXISTS `ecommerce_migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ecommerce_migrations_migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas exclusivas de e-commerce. company_id es una referencia lógica a administracion.companies.id.
-- No hay llave foránea ni consulta de tablas internas del POS.
CREATE TABLE IF NOT EXISTS `ecommerce_storefronts` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `domain` varchar(255) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'enabled',
    `flat_shipping` decimal(12,2) NOT NULL DEFAULT 0.00,
    `coupons` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ecommerce_storefronts_domain_unique` (`domain`),
    KEY `ecommerce_storefronts_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ecommerce_catalog_products` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `pos_product_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `sku` varchar(100) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `image_url` varchar(255) DEFAULT NULL,
    `price` decimal(12,2) NOT NULL,
    `currency` varchar(10) NOT NULL,
    `tax_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
    `stock` decimal(14,3) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `payload` json DEFAULT NULL,
    `synced_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ecommerce_catalog_products_company_product_unique` (`company_id`, `pos_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ecommerce_customer_profiles` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `keycloak_subject` char(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `phone` varchar(32) DEFAULT NULL,
    `address` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ecommerce_customer_profiles_company_subject_unique` (`company_id`, `keycloak_subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ecommerce_checkout_orders` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `external_order_uuid` char(36) NOT NULL,
    `external_customer_uuid` char(36) NOT NULL,
    `pos_sale_id` bigint unsigned DEFAULT NULL,
    `status` varchar(30) NOT NULL DEFAULT 'pending_pos',
    `total` decimal(12,2) DEFAULT NULL,
    `currency` varchar(10) DEFAULT NULL,
    `request_payload` json NOT NULL,
    `pos_response` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `checkout_orders_external_order_uuid_unique` (`external_order_uuid`),
    KEY `ecommerce_checkout_orders_company_status_index` (`company_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ecommerce_inbound_events` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `event_uuid` char(36) NOT NULL,
    `event_type` varchar(100) NOT NULL,
    `payload` json NOT NULL,
    `processed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `inbound_events_event_uuid_unique` (`event_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Impide que Laravel vuelva a ejecutar las migraciones ya representadas aquí.
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_10_15_000000_add_platform_integration_fields', 999);

INSERT IGNORE INTO `ecommerce_migrations` (`migration`, `batch`)
VALUES
    ('2026_10_15_000000_create_ecommerce_platform_tables', 1),
    ('2026_10_18_020000_replace_tenant_uuid_with_administrative_company_id', 1);

SET FOREIGN_KEY_CHECKS = 1;
