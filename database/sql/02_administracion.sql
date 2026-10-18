-- Base administrativa independiente.
-- Destino: una base nueva, por ejemplo usuario_administracion.
-- Ejecutar UNA sola vez desde phpMyAdmin o el cliente MySQL. No elimina datos.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `administracion_migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `administracion_migrations_migration_unique` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `remember_token` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint unsigned DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `payload` longtext NOT NULL,
    `last_activity` int NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
    `key` varchar(255) NOT NULL,
    `value` mediumtext NOT NULL,
    `expiration` int NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` varchar(255) NOT NULL,
    `owner` varchar(255) NOT NULL,
    `expiration` int NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `queue` varchar(255) NOT NULL,
    `payload` longtext NOT NULL,
    `attempts` tinyint unsigned NOT NULL,
    `reserved_at` int unsigned DEFAULT NULL,
    `available_at` int unsigned NOT NULL,
    `created_at` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `total_jobs` int NOT NULL,
    `pending_jobs` int NOT NULL,
    `failed_jobs` int NOT NULL,
    `failed_job_ids` longtext NOT NULL,
    `options` mediumtext DEFAULT NULL,
    `cancelled_at` int DEFAULT NULL,
    `created_at` int NOT NULL,
    `finished_at` int DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `uuid` varchar(255) NOT NULL,
    `connection` text NOT NULL,
    `queue` text NOT NULL,
    `payload` longtext NOT NULL,
    `exception` longtext NOT NULL,
    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `companies` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `tenant_uuid` char(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `domain` varchar(255) DEFAULT NULL,
    `identification` varchar(100) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `phone` varchar(50) DEFAULT NULL,
    `address` varchar(255) DEFAULT NULL,
    `company_type_id` bigint unsigned DEFAULT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'active',
    `kickoff_completed` tinyint(1) NOT NULL DEFAULT 0,
    `primary_admin_name` varchar(255) DEFAULT NULL,
    `primary_admin_email` varchar(255) DEFAULT NULL,
    `keycloak_subject` char(36) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `companies_tenant_uuid_unique` (`tenant_uuid`),
    UNIQUE KEY `companies_domain_unique` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `platform_services` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `slug` varchar(50) NOT NULL,
    `name` varchar(255) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `platform_services_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plans` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `platform_service_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `slug` varchar(80) NOT NULL,
    `billing_period` varchar(20) NOT NULL,
    `price` decimal(12,2) NOT NULL DEFAULT 0.00,
    `currency` varchar(10) NOT NULL DEFAULT 'USD',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `plans_slug_unique` (`slug`),
    CONSTRAINT `plans_platform_service_id_foreign`
        FOREIGN KEY (`platform_service_id`) REFERENCES `platform_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `platform_service_id` bigint unsigned NOT NULL,
    `plan_id` bigint unsigned DEFAULT NULL,
    `status` varchar(30) NOT NULL DEFAULT 'active',
    `payment_status` varchar(30) NOT NULL DEFAULT 'paid',
    `starts_at` date NOT NULL,
    `ends_at` date DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `subscriptions_company_status_ends_index` (`company_id`, `status`, `ends_at`),
    CONSTRAINT `subscriptions_platform_service_id_foreign`
        FOREIGN KEY (`platform_service_id`) REFERENCES `platform_services` (`id`) ON DELETE CASCADE,
    CONSTRAINT `subscriptions_plan_id_foreign`
        FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `entitlements` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `service` varchar(50) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'enabled',
    `expires_at` timestamp NULL DEFAULT NULL,
    `payload` json DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `entitlements_company_service_unique` (`company_id`, `service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_domains` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `domain` varchar(255) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'pending',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tenant_domains_domain_unique` (`domain`),
    KEY `tenant_domains_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `provisioning_jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `company_id` bigint unsigned NOT NULL,
    `target` varchar(50) NOT NULL,
    `action` varchar(50) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'pending',
    `attempts` int unsigned NOT NULL DEFAULT 0,
    `payload` json NOT NULL,
    `last_error` text DEFAULT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `provisioning_jobs_status_target_index` (`status`, `target`),
    KEY `provisioning_jobs_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registra las migraciones incluidas para que `php artisan migrate` no las repita.
INSERT IGNORE INTO `administracion_migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_10_15_000000_create_platform_administration_tables', 1),
    ('2026_10_18_010000_rename_tenants_as_companies_and_remove_company_foreign_keys', 1);
