-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: saturnext
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounting_accounts`
--

DROP TABLE IF EXISTS `accounting_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `nature` varchar(10) NOT NULL,
  `parent_account_id` bigint(20) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `is_postable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_accounts_code_unique` (`code`),
  KEY `accounting_accounts_parent_account_id_foreign` (`parent_account_id`),
  CONSTRAINT `accounting_accounts_parent_account_id_foreign` FOREIGN KEY (`parent_account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_accounts`
--

LOCK TABLES `accounting_accounts` WRITE;
/*!40000 ALTER TABLE `accounting_accounts` DISABLE KEYS */;
INSERT INTO `accounting_accounts` VALUES (1,'1','Activo','asset','debit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(2,'11','Disponible','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(3,'1105','Caja','asset','debit',2,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(4,'1110','Bancos','asset','debit',2,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(5,'13','Deudores','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(6,'1305','Clientes','asset','debit',5,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(7,'14','Inventarios','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(8,'1435','Mercancias no fabricadas','asset','debit',7,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(9,'2','Pasivo','liability','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(10,'22','Proveedores','liability','credit',9,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(11,'2205','Nacionales','liability','credit',10,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(12,'24','Impuestos por pagar','liability','credit',9,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(13,'2408','IVA por pagar','liability','credit',12,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(14,'3','Patrimonio','equity','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(15,'31','Capital social','equity','credit',14,2,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(16,'4','Ingresos','income','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(17,'41','Ingresos operacionales','income','credit',16,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(18,'4135','Comercio al por menor','income','credit',17,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(19,'5','Gastos','expense','debit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(20,'51','Administracion','expense','debit',19,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(21,'5105','Gastos de personal','expense','debit',20,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(22,'5135','Servicios','expense','debit',20,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(23,'6','Costo de ventas','expense','debit',NULL,1,0,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(24,'61','Costo de mercancias vendidas','expense','debit',23,2,0,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(25,'6135','Comercio al por menor','expense','debit',24,4,1,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(26,'36','Resultados del ejercicio','equity','credit',14,2,0,1,'2026-02-13 09:22:52','2026-02-13 09:22:52'),(27,'3605','Utilidad o perdida del ejercicio','equity','credit',26,4,1,1,'2026-02-13 09:22:52','2026-02-13 09:22:52');
/*!40000 ALTER TABLE `accounting_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounting_period_closures`
--

DROP TABLE IF EXISTS `accounting_period_closures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_period_closures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `entry_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `net_income` decimal(14,2) NOT NULL DEFAULT 0.00,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_period_closures_from_date_to_date_unique` (`from_date`,`to_date`),
  KEY `accounting_period_closures_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `accounting_period_closures_user_id_foreign` (`user_id`),
  CONSTRAINT `accounting_period_closures_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`),
  CONSTRAINT `accounting_period_closures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_period_closures`
--

LOCK TABLES `accounting_period_closures` WRITE;
/*!40000 ALTER TABLE `accounting_period_closures` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounting_period_closures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(32) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Sucursal Principal','PRN','Direccion principal','000-0000','2026-02-12 10:08:21','2026-02-14 21:48:06');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_movements`
--

DROP TABLE IF EXISTS `cash_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cash_register_session_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` enum('IN','OUT') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_movements_branch_id_foreign` (`branch_id`),
  KEY `cash_movements_user_id_foreign` (`user_id`),
  KEY `cash_movements_cash_register_session_id_type_index` (`cash_register_session_id`,`type`),
  CONSTRAINT `cash_movements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_movements_cash_register_session_id_foreign` FOREIGN KEY (`cash_register_session_id`) REFERENCES `cash_register_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_movements`
--

LOCK TABLES `cash_movements` WRITE;
/*!40000 ALTER TABLE `cash_movements` DISABLE KEYS */;
INSERT INTO `cash_movements` VALUES (2,2,1,4,'IN',2160.00,'Venta Punto de venta - efectivo','2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:44:45','2026-02-15 23:44:45'),(4,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:44:47','2026-02-15 23:44:47'),(5,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:45:22','2026-02-15 23:45:22'),(6,2,1,4,'IN',2160.00,'Venta Punto de venta - efectivo','2026-02-15 23:50:23','2026-02-15 23:50:23'),(7,2,1,4,'OUT',5000.00,'Gasto: Compra de camara','2026-02-16 00:05:12','2026-02-16 00:05:12');
/*!40000 ALTER TABLE `cash_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_register_sessions`
--

DROP TABLE IF EXISTS `cash_register_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_register_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `opening_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `closing_amount` decimal(12,2) DEFAULT NULL,
  `expected_amount` decimal(12,2) DEFAULT NULL,
  `difference` decimal(12,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_register_sessions_user_id_foreign` (`user_id`),
  KEY `cash_register_sessions_branch_id_user_id_status_index` (`branch_id`,`user_id`,`status`),
  CONSTRAINT `cash_register_sessions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_register_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_register_sessions`
--

LOCK TABLES `cash_register_sessions` WRITE;
/*!40000 ALTER TABLE `cash_register_sessions` DISABLE KEYS */;
INSERT INTO `cash_register_sessions` VALUES (1,1,4,'2026-02-13 10:12:17','2026-02-13 17:08:19',100000.00,1200000.00,100000.00,1100000.00,'closed','2026-02-13 10:12:17','2026-02-13 17:08:19'),(2,1,4,'2026-02-15 02:15:41',NULL,100000.00,NULL,NULL,NULL,'open','2026-02-15 02:15:41','2026-02-15 02:15:41');
/*!40000 ALTER TABLE `cash_register_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Mecatos',NULL,NULL,'2026-02-12 10:23:29','2026-02-12 10:23:29'),(2,'Bebidas','Bebidas y refrescos',NULL,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(3,'Snacks','Snacks y botanas',NULL,'2026-02-14 21:48:06','2026-02-14 21:48:06');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `document` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_type` varchar(20) NOT NULL DEFAULT 'person',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_user_id_unique` (`user_id`),
  KEY `customers_document_index` (`document`),
  KEY `customers_email_index` (`email`),
  KEY `customers_contact_type_index` (`contact_type`),
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,NULL,'Consumidor final','222222222222',NULL,NULL,NULL,'person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(2,NULL,'Cliente Mostrador','CF',NULL,NULL,NULL,'person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(3,NULL,'Empresa Demo','NIT-123456','facturas@demo.com','555-0303','Zona Industrial','person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(4,7,'Libis marquez',NULL,'libis@gmail.com','3214555115','Calle Principal 123','person',1,'2026-02-15 08:02:15','2026-02-15 08:23:45',NULL),(5,NULL,'proveedor mas','1112200','elproveedor@gmail.com','3215222141','Direccion principal','supplier',1,'2026-02-16 07:44:12','2026-02-16 07:44:12',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `min_stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_branch_id_product_id_unique` (`branch_id`,`product_id`),
  KEY `inventories_product_id_foreign` (`product_id`),
  CONSTRAINT `inventories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` VALUES (1,1,1,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(2,1,2,98.000,10.000,'2026-02-14 21:48:06','2026-02-16 09:54:03'),(3,1,3,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(4,1,4,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(5,1,5,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(6,1,6,93.000,10.000,'2026-02-14 21:48:06','2026-02-16 09:54:03'),(7,1,7,30.000,5.000,'2026-02-14 23:59:48','2026-02-14 23:59:48'),(8,1,8,28.000,5.000,'2026-02-14 23:59:48','2026-02-16 06:55:27'),(9,1,9,29.000,5.000,'2026-02-14 23:59:48','2026-02-15 08:23:46'),(10,1,10,26.000,5.000,'2026-02-14 23:59:48','2026-02-16 09:41:56'),(11,1,13,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(12,1,14,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(13,1,15,29.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:23:46'),(14,1,16,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(15,1,17,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('IN','OUT') NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ref_type` varchar(64) DEFAULT NULL,
  `ref_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_product_id_foreign` (`product_id`),
  KEY `inventory_movements_user_id_foreign` (`user_id`),
  KEY `inventory_movements_branch_id_product_id_index` (`branch_id`,`product_id`),
  KEY `inventory_movements_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  CONSTRAINT `inventory_movements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
INSERT INTO `inventory_movements` VALUES (2,1,2,4,'IN',100.000,800.00,'manual',NULL,NULL,'2026-02-15 02:23:59','2026-02-15 02:23:59'),(3,1,2,4,'OUT',100.000,800.00,'manual',NULL,NULL,'2026-02-15 02:25:21','2026-02-15 02:25:21'),(4,1,9,7,'OUT',1.000,9.80,'sale',2,'Venta ecommerce','2026-02-15 08:23:46','2026-02-15 08:23:46'),(5,1,15,7,'OUT',1.000,10.20,'sale',2,'Venta ecommerce','2026-02-15 08:23:46','2026-02-15 08:23:46'),(6,1,2,4,'OUT',1.000,0.40,'sale',3,'Venta Punto de venta','2026-02-15 08:48:15','2026-02-15 08:48:15'),(7,1,6,4,'OUT',1.000,0.80,'sale',3,'Venta Punto de venta','2026-02-15 08:48:15','2026-02-15 08:48:15'),(8,1,2,4,'IN',1.000,200.00,'purchase',1,'Ingreso por compra','2026-02-15 09:19:09','2026-02-15 09:19:09'),(9,1,6,4,'OUT',1.000,0.80,'sale',4,'Venta Punto de venta','2026-02-15 23:44:45','2026-02-15 23:44:45'),(10,1,10,4,'OUT',1.000,4.50,'sale',4,'Venta Punto de venta','2026-02-15 23:44:45','2026-02-15 23:44:45'),(11,1,6,4,'OUT',1.000,0.80,'sale',5,'Venta Punto de venta','2026-02-15 23:44:47','2026-02-15 23:44:47'),(12,1,10,4,'OUT',1.000,4.50,'sale',5,'Venta Punto de venta','2026-02-15 23:44:47','2026-02-15 23:44:47'),(13,1,6,4,'OUT',1.000,0.80,'sale',6,'Venta Punto de venta','2026-02-15 23:45:22','2026-02-15 23:45:22'),(14,1,10,4,'OUT',1.000,4.50,'sale',6,'Venta Punto de venta','2026-02-15 23:45:22','2026-02-15 23:45:22'),(15,1,2,4,'OUT',1.000,200.00,'sale',7,'Venta Punto de venta','2026-02-15 23:50:23','2026-02-15 23:50:23'),(16,1,8,4,'OUT',1.000,4.20,'sale',7,'Venta Punto de venta','2026-02-15 23:50:23','2026-02-15 23:50:23'),(17,1,6,4,'OUT',1.000,0.80,'sale',8,'Venta Punto de venta','2026-02-16 06:55:26','2026-02-16 06:55:26'),(18,1,8,4,'OUT',1.000,4.20,'sale',8,'Venta Punto de venta','2026-02-16 06:55:27','2026-02-16 06:55:27'),(19,1,2,4,'IN',1.000,200.00,'purchase',2,'Ingreso por compra','2026-02-16 07:00:07','2026-02-16 07:00:07'),(20,1,2,7,'OUT',1.000,200.00,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(21,1,6,7,'OUT',1.000,0.80,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(22,1,10,7,'OUT',1.000,4.50,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(23,1,2,7,'OUT',1.000,200.00,'sale',10,'Venta ecommerce','2026-02-16 09:54:03','2026-02-16 09:54:03'),(24,1,6,7,'OUT',1.000,0.80,'sale',10,'Venta ecommerce','2026-02-16 09:54:03','2026-02-16 09:54:03');
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_number` varchar(30) NOT NULL,
  `entry_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'posted',
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_entry_number_unique` (`entry_number`),
  KEY `journal_entries_user_id_foreign` (`user_id`),
  CONSTRAINT `journal_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (2,'VS-20260215-0001','2026-02-15','Venta POS #2','posted',4,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,'CP-20260215-0001','2026-02-15','Compra #1','posted',4,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(4,'VS-20260215-0002','2026-02-15','Venta POS #3','posted',4,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(5,'VS-20260215-0003','2026-02-15','Venta POS #4','posted',4,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,'VS-20260215-0004','2026-02-15','Venta POS #5','posted',4,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,'VS-20260215-0005','2026-02-15','Venta POS #6','posted',4,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,'GS-20260215-0001','2026-02-15','Gasto: Compra de camara','posted',4,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(9,'VS-20260216-0001','2026-02-16','Venta POS #7','posted',4,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(10,'CP-20260216-0001','2026-02-16','Compra #2','posted',4,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(11,'PG-20260216-0001','2026-02-16','Pago cartera compra #2','posted',4,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(12,'VS-20260216-0002','2026-02-15','Venta POS #1','posted',4,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(13,'VS-20260216-0003','2026-02-16','Venta POS #8','posted',4,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(14,'VS-20260216-0004','2026-02-16','Venta POS #9','posted',4,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(15,'VS-20260216-0005','2026-02-15','Venta POS #1','posted',4,'2026-02-16 11:02:44','2026-02-16 11:02:44');
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entry_lines`
--

DROP TABLE IF EXISTS `journal_entry_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entry_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `accounting_account_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_lines_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `journal_entry_lines_accounting_account_id_foreign` (`accounting_account_id`),
  CONSTRAINT `journal_entry_lines_accounting_account_id_foreign` FOREIGN KEY (`accounting_account_id`) REFERENCES `accounting_accounts` (`id`),
  CONSTRAINT `journal_entry_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entry_lines`
--

LOCK TABLES `journal_entry_lines` WRITE;
/*!40000 ALTER TABLE `journal_entry_lines` DISABLE KEYS */;
INSERT INTO `journal_entry_lines` VALUES (2,2,3,'Ingreso por venta - efectivo',2160.00,0.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,2,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,2,13,'IVA generado en venta',0.00,160.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(5,2,25,'Costo de venta',1.20,0.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(6,2,8,'Salida de inventario por venta',0.00,1.20,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(7,3,8,'Ingreso de inventario por compra',200.00,0.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(8,3,3,'Pago compra en efectivo',0.00,200.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(9,4,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(10,4,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(11,4,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(12,4,25,'Costo de venta',5.30,0.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(13,4,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(14,5,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(15,5,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(16,5,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(17,5,25,'Costo de venta',5.30,0.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(18,5,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(19,6,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(20,6,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(21,6,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(22,6,25,'Costo de venta',5.30,0.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(23,6,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(24,7,3,'Ingreso por venta - efectivo',2160.00,0.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(25,7,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(26,7,13,'IVA generado en venta',0.00,160.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(27,7,25,'Costo de venta',204.20,0.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(28,7,8,'Salida de inventario por venta',0.00,204.20,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(29,8,21,'Registro gasto: Compra de camara',5000.00,0.00,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(30,8,3,'Salida por gasto - caja',0.00,5000.00,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(31,9,6,'Cuenta por cobrar venta',2320.00,0.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(32,9,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(33,9,13,'IVA generado en venta',0.00,320.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(34,9,25,'Costo de venta',5.00,0.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(35,9,8,'Salida de inventario por venta',0.00,5.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(36,10,8,'Ingreso de inventario por compra',200.00,0.00,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(37,10,11,'Cuenta por pagar proveedor',0.00,200.00,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(38,11,11,'Disminucion cuenta por pagar',100.00,0.00,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(39,11,4,'Pago cartera proveedor',0.00,100.00,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(40,12,4,'Ingreso por venta - bancos',50.84,0.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(41,12,18,'Ingreso operativo por venta',0.00,45.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(42,12,13,'IVA generado en venta',0.00,5.84,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(43,12,25,'Costo de venta',20.00,0.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(44,12,8,'Salida de inventario por venta',0.00,20.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(45,13,6,'Cuenta por cobrar venta',20.88,0.00,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(46,13,18,'Ingreso operativo por venta',0.00,19.30,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(47,13,13,'IVA generado en venta',0.00,1.58,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(48,13,25,'Costo de venta',205.30,0.00,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(49,13,8,'Salida de inventario por venta',0.00,205.30,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(50,14,6,'Cuenta por cobrar venta',11.37,0.00,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(51,14,18,'Ingreso operativo por venta',0.00,11.10,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(52,14,13,'IVA generado en venta',0.00,0.27,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(53,14,25,'Costo de venta',200.80,0.00,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(54,14,8,'Salida de inventario por venta',0.00,200.80,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(55,15,4,'Ingreso por venta - bancos',50.84,0.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(56,15,18,'Ingreso operativo por venta',0.00,45.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(57,15,13,'IVA generado en venta',0.00,5.84,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(58,15,25,'Costo de venta',20.00,0.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(59,15,8,'Salida de inventario por venta',0.00,20.00,'2026-02-16 11:02:44','2026-02-16 11:02:44');
/*!40000 ALTER TABLE `journal_entry_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_11_034320_create_permission_tables',1),(5,'2026_02_11_100000_create_branches_table',1),(6,'2026_02_11_100010_create_categories_table',1),(7,'2026_02_11_100020_create_taxes_table',1),(8,'2026_02_11_100030_create_customers_table',1),(9,'2026_02_11_100040_create_products_table',1),(10,'2026_02_11_100050_create_inventories_table',1),(11,'2026_02_11_100060_create_inventory_movements_table',1),(12,'2026_02_11_100065_create_cash_register_sessions_table',1),(13,'2026_02_11_100070_create_sales_table',1),(14,'2026_02_11_100080_create_sale_items_table',1),(15,'2026_02_11_100081_create_payments_table',1),(16,'2026_02_11_100090_create_cash_movements_table',1),(17,'2026_02_11_100100_create_settings_table',1),(18,'2026_02_11_100110_create_returns_table',1),(19,'2026_02_11_100120_create_return_items_table',1),(20,'2026_02_11_100130_add_branch_id_to_users_table',1),(21,'2026_02_13_032500_add_product_types_and_kit_items',2),(22,'2026_02_13_040000_create_accounting_tables',3),(23,'2026_02_13_041000_add_puc_fields_to_accounting_accounts',4),(24,'2026_02_13_043000_create_accounting_period_closures_table',5),(25,'2026_02_14_120000_add_user_id_to_customers_table',6),(26,'2026_02_14_130000_add_ecommerce_fields_to_sales_table',6),(27,'2026_02_14_131000_add_image_url_to_products_table',6),(28,'2026_02_14_132000_add_is_visible_ecommerce_to_products_table',7),(29,'2026_02_15_040000_create_purchases_tables',8),(30,'2026_02_15_180000_create_purchase_payments_table',9),(31,'2026_02_16_120000_add_contact_type_to_customers_table',10),(32,'2026_02_16_120000_add_void_fields_to_credit_payments',11),(33,'2026_02_16_123000_add_invoice_fields_to_sales_table',12),(34,'2026_02_16_130000_add_accounting_fields_to_sales_table',13);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',4),(2,'App\\Models\\User',5),(3,'App\\Models\\User',6),(4,'App\\Models\\User',7);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('admin@pos.test','$2y$12$eLFwTftPRsREeIJapiJ86eBbBGWHgVedwr8UPlurPh42QTU.1qn/2','2026-02-17 06:42:37');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_sale_id_method_index` (`sale_id`,`method`),
  KEY `payments_voided_by_user_id_foreign` (`voided_by_user_id`),
  KEY `payments_sale_id_voided_at_index` (`sale_id`,`voided_at`),
  CONSTRAINT `payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_voided_by_user_id_foreign` FOREIGN KEY (`voided_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,2,'card',50.84,'E-COMMERCE','2026-02-15 08:23:46',NULL,NULL,NULL,'2026-02-15 08:23:46','2026-02-15 08:23:46'),(3,3,'cash',2160.00,NULL,'2026-02-15 08:48:15',NULL,NULL,NULL,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,4,'cash',232.00,NULL,'2026-02-15 23:44:45',NULL,NULL,NULL,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(5,5,'cash',232.00,NULL,'2026-02-15 23:44:47',NULL,NULL,NULL,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,6,'cash',232.00,NULL,'2026-02-15 23:45:22',NULL,NULL,NULL,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,7,'cash',2160.00,NULL,'2026-02-15 23:50:23',NULL,NULL,NULL,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,8,'credit',2320.00,NULL,'2026-02-16 06:55:27',NULL,NULL,NULL,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(9,9,'qr',20.88,'FlDKK5557141','2026-02-16 09:41:56',NULL,NULL,NULL,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(10,10,'qr',11.37,'FlDKK55571411','2026-02-16 09:54:03',NULL,NULL,NULL,'2026-02-16 09:54:03','2026-02-16 09:54:03');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage_users','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(2,'manage_settings','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(3,'manage_products','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(4,'manage_categories','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(5,'manage_customers','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(6,'manage_inventory','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(7,'view_reports','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(8,'open_cash_register','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(9,'close_cash_register','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(10,'record_cash_movement','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(11,'create_sale','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(12,'apply_discount','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(13,'apply_high_discount','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(14,'void_sale','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(15,'process_return','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(16,'manage_accounting','web','2026-02-13 09:01:26','2026-02-13 09:01:26'),(17,'manage_branches','web','2026-02-13 09:43:09','2026-02-13 09:43:09'),(18,'manage_ecommerce_orders','web','2026-02-14 21:48:05','2026-02-14 21:48:05'),(19,'manage_purchases','web','2026-02-15 09:01:12','2026-02-15 09:01:12');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_kit_items`
--

DROP TABLE IF EXISTS `product_kit_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_kit_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kit_product_id` bigint(20) unsigned NOT NULL,
  `component_product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_kit_items_kit_product_id_component_product_id_unique` (`kit_product_id`,`component_product_id`),
  KEY `product_kit_items_component_product_id_foreign` (`component_product_id`),
  CONSTRAINT `product_kit_items_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_kit_items_kit_product_id_foreign` FOREIGN KEY (`kit_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_kit_items`
--

LOCK TABLES `product_kit_items` WRITE;
/*!40000 ALTER TABLE `product_kit_items` DISABLE KEYS */;
INSERT INTO `product_kit_items` VALUES (14,11,2,1.000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(15,11,1,1.000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(16,12,5,1.000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(17,12,6,1.000,'2026-02-15 08:11:43','2026-02-15 08:11:43');
/*!40000 ALTER TABLE `product_kit_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `tax_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit` varchar(32) NOT NULL DEFAULT 'unit',
  `product_type` varchar(16) NOT NULL DEFAULT 'simple',
  `parent_product_id` bigint(20) unsigned DEFAULT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible_ecommerce` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_tax_id_foreign` (`tax_id`),
  KEY `products_parent_product_id_foreign` (`parent_product_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_parent_product_id_foreign` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,2,1,'Refresco Cola 600ml','BEB-0001','7501000000011','/images/products/cola.svg','Bebida gaseosa sabor cola','unit','simple',NULL,0.60,1.20,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(2,2,2,'Agua 600ml','BEB-0002','7501000000028','/images/products/agua.svg','Agua purificada sin gas','unit','simple',NULL,200.00,0.90,1,1,'2026-02-14 21:48:06','2026-02-16 07:00:07',NULL),(3,2,1,'Cafe Molido Premium 250g','BEB-0003','7501000000035','/images/products/cafe.svg','Cafe tostado molido premium','unit','simple',NULL,2.40,4.80,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(4,3,1,'Papas Fritas 150g','SNK-0001','7501000000042','/images/products/papas.svg','Snack salado crujiente','unit','simple',NULL,0.70,1.50,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(5,3,1,'Galletas Chocolate 120g','SNK-0002','7501000000059','/images/products/galletas.svg','Galletas rellenas de chocolate','unit','simple',NULL,0.90,1.90,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(6,3,1,'Barra de Chocolate 90g','SNK-0003','7501000000066','/images/products/chocolate.svg','Chocolate semiamargo','unit','simple',NULL,0.80,1.70,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(7,3,1,'Camiseta deportiva base','BAS-TSHIRT','7501000001001','/images/product-placeholder.svg','Producto base para variantes de talla.','unit','simple',NULL,9.50,16.00,1,0,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(8,2,1,'Cafe base origen','BAS-COFFEE','7501000001002','/images/products/cafe.svg','Producto base para variantes de molienda.','unit','simple',NULL,4.20,7.50,1,0,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(9,3,1,'Camiseta deportiva talla M','VAR-TSHIRT-M','7501000001011','/images/product-placeholder.svg','Variante talla M.','unit','variant',7,9.80,17.90,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(10,2,1,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012','/images/products/cafe.svg','Variante en grano.','unit','variant',8,4.50,8.20,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(11,2,1,'Kit hidratacion','KIT-HIDRA','7501000001021','/images/products/agua.svg','Incluye 1 agua + 1 refresco.','unit','kit',NULL,0.00,1.95,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(12,3,1,'Kit snack dulce','KIT-SNACK','7501000001022','/images/products/galletas.svg','Incluye galletas + barra de chocolate.','unit','kit',NULL,0.00,3.30,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(13,3,1,'Camiseta deportiva talla S','VAR-TSHIRT-S','7501000001010','/images/product-placeholder.svg','Variante talla S.','unit','variant',7,9.60,17.50,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(14,3,1,'Camiseta deportiva talla L','VAR-TSHIRT-L','7501000001013','/images/product-placeholder.svg','Variante talla L.','unit','variant',7,10.00,18.20,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(15,3,1,'Camiseta deportiva talla XL','VAR-TSHIRT-XL','7501000001014','/images/product-placeholder.svg','Variante talla XL.','unit','variant',7,10.20,18.60,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(16,2,1,'Cafe origen molido 500g','VAR-COFFEE-MOLIDO','7501000001015','/images/products/cafe.svg','Variante molido tradicional.','unit','variant',8,4.40,8.10,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(17,2,1,'Cafe descafeinado 500g','VAR-COFFEE-DESCAFEINADO','7501000001016','/images/products/cafe.svg','Variante descafeinado.','unit','variant',8,4.70,8.60,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `tax_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,2,'Agua 600ml','BEB-0002',1.000,200.00,0.00,0.00,200.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(2,2,2,'Agua 600ml','BEB-0002',1.000,200.00,0.00,0.00,200.00,'2026-02-16 07:00:07','2026-02-16 07:00:07');
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_payments`
--

DROP TABLE IF EXISTS `purchase_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payments_user_id_foreign` (`user_id`),
  KEY `purchase_payments_purchase_id_method_index` (`purchase_id`,`method`),
  KEY `purchase_payments_voided_by_user_id_foreign` (`voided_by_user_id`),
  KEY `purchase_payments_purchase_id_voided_at_index` (`purchase_id`,`voided_at`),
  CONSTRAINT `purchase_payments_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_payments_voided_by_user_id_foreign` FOREIGN KEY (`voided_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_payments`
--

LOCK TABLES `purchase_payments` WRITE;
/*!40000 ALTER TABLE `purchase_payments` DISABLE KEYS */;
INSERT INTO `purchase_payments` VALUES (1,2,4,'transfer',100.00,'k441','2026-02-16 07:01:20',NULL,NULL,NULL,'2026-02-16 07:01:20','2026-02-16 07:01:20');
/*!40000 ALTER TABLE `purchase_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `purchase_number` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'posted',
  `supplier_name` varchar(255) NOT NULL,
  `supplier_document` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) NOT NULL DEFAULT 'credit',
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_branch_id_purchase_number_unique` (`branch_id`,`purchase_number`),
  KEY `purchases_user_id_foreign` (`user_id`),
  KEY `purchases_branch_id_purchased_at_index` (`branch_id`,`purchased_at`),
  CONSTRAINT `purchases_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,1,4,1,'posted','aguas','10520262','5525541',200.00,0.00,200.00,200.00,0.00,'cash','2026-02-15 09:19:09',NULL,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(2,1,4,2,'posted','Libis','10520262','55255411',200.00,0.00,200.00,100.00,100.00,'credit','2026-02-16 07:00:07',NULL,'2026-02-16 07:00:07','2026-02-16 07:01:20');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_items`
--

DROP TABLE IF EXISTS `return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_items_return_id_foreign` (`return_id`),
  KEY `return_items_product_id_foreign` (`product_id`),
  CONSTRAINT `return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `return_items_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_items`
--

LOCK TABLES `return_items` WRITE;
/*!40000 ALTER TABLE `return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `returns_sale_id_foreign` (`sale_id`),
  KEY `returns_user_id_foreign` (`user_id`),
  KEY `returns_branch_id_created_at_index` (`branch_id`,`created_at`),
  CONSTRAINT `returns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `returns_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(3,2),(4,1),(4,2),(5,1),(5,2),(6,1),(6,2),(7,1),(7,2),(8,1),(8,2),(8,3),(9,1),(9,2),(9,3),(10,1),(10,2),(10,3),(11,1),(11,2),(11,3),(12,1),(12,2),(12,3),(13,1),(13,2),(14,1),(14,2),(15,1),(15,2),(16,1),(16,2),(17,1),(17,2),(18,1),(18,2),(19,1),(19,2);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(2,'supervisor','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(3,'cashier','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(4,'customer','web','2026-02-14 21:48:05','2026-02-14 21:48:05');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(64) DEFAULT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount_type` enum('percent','fixed') DEFAULT NULL,
  `discount_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_sale_id_product_id_index` (`sale_id`,`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (2,2,9,'Camiseta deportiva talla M','VAR-TSHIRT-M','7501000001011',1.000,17.90,NULL,0.00,16.00,2.86,20.76,'2026-02-15 08:23:45','2026-02-15 08:23:45'),(3,2,15,'Camiseta deportiva talla XL','VAR-TSHIRT-XL','7501000001014',1.000,18.60,NULL,0.00,16.00,2.98,21.58,'2026-02-15 08:23:46','2026-02-15 08:23:46'),(4,3,2,'Agua 600ml','BEB-0002','7501000000028',1.000,1000.00,NULL,0.00,0.00,0.00,1000.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(5,3,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(6,4,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:44','2026-02-15 23:44:44'),(7,4,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(8,5,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(9,5,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(10,6,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(11,6,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(12,7,2,'Agua 600ml','BEB-0002','7501000000028',1.000,1000.00,NULL,0.00,0.00,0.00,1000.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(13,7,8,'Cafe base origen','BAS-COFFEE','7501000001002',1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(14,8,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-16 06:55:26','2026-02-16 06:55:26'),(15,8,8,'Cafe base origen','BAS-COFFEE','7501000001002',1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(16,9,2,'Agua 600ml','BEB-0002','7501000000028',1.000,0.90,NULL,0.00,0.00,0.00,0.90,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(17,9,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,1.70,NULL,0.00,16.00,0.27,1.97,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(18,9,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',1.000,8.20,NULL,0.00,16.00,1.31,9.51,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(19,10,2,'Agua 600ml','BEB-0002','7501000000028',1.000,0.90,NULL,0.00,0.00,0.00,0.90,'2026-02-16 09:54:03','2026-02-16 09:54:03'),(20,10,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',1.000,1.70,NULL,0.00,16.00,0.27,1.97,'2026-02-16 09:54:03','2026-02-16 09:54:03');
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `cash_register_session_id` bigint(20) unsigned DEFAULT NULL,
  `sale_number` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'paid',
  `order_source` varchar(20) NOT NULL DEFAULT 'pos',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `customer_note` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `sold_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `invoiced_at` timestamp NULL DEFAULT NULL,
  `invoiced_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `accounted_at` timestamp NULL DEFAULT NULL,
  `accounted_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_branch_id_sale_number_unique` (`branch_id`,`sale_number`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_cash_register_session_id_foreign` (`cash_register_session_id`),
  KEY `sales_branch_id_sold_at_index` (`branch_id`,`sold_at`),
  KEY `sales_order_source_index` (`order_source`),
  KEY `sales_invoiced_by_user_id_foreign` (`invoiced_by_user_id`),
  KEY `sales_invoiced_at_index` (`invoiced_at`),
  KEY `sales_accounted_by_user_id_foreign` (`accounted_by_user_id`),
  KEY `sales_accounted_at_index` (`accounted_at`),
  CONSTRAINT `sales_accounted_by_user_id_foreign` FOREIGN KEY (`accounted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_cash_register_session_id_foreign` FOREIGN KEY (`cash_register_session_id`) REFERENCES `cash_register_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_invoiced_by_user_id_foreign` FOREIGN KEY (`invoiced_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (2,1,7,4,NULL,1,'pending','ecommerce',36.50,0.00,5.84,8.50,0.00,NULL,'Calle Principal 123',NULL,50.84,50.84,0.00,'USD','2026-02-15 08:23:45','2026-02-16 09:49:06',4,'2026-02-16 11:02:44',4,'2026-02-15 08:23:45','2026-02-16 11:02:44'),(3,1,4,NULL,2,2,'paid','pos',2000.00,0.00,160.00,0.00,0.00,NULL,NULL,NULL,2160.00,2160.00,0.00,'USD','2026-02-15 08:48:15',NULL,NULL,NULL,NULL,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,1,4,1,2,3,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-02-15 23:44:44',NULL,NULL,NULL,NULL,'2026-02-15 23:44:44','2026-02-15 23:44:44'),(5,1,4,1,2,4,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-02-15 23:44:47',NULL,NULL,NULL,NULL,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,1,4,NULL,2,5,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-02-15 23:45:22',NULL,NULL,NULL,NULL,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,1,4,NULL,2,6,'paid','pos',2000.00,0.00,160.00,0.00,0.00,NULL,NULL,NULL,2160.00,2160.00,0.00,'USD','2026-02-15 23:50:23',NULL,NULL,NULL,NULL,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,1,4,1,2,7,'pending','pos',2000.00,0.00,320.00,0.00,0.00,NULL,NULL,NULL,2320.00,0.00,0.00,'USD','2026-02-16 06:55:26',NULL,NULL,NULL,NULL,'2026-02-16 06:55:26','2026-02-16 06:55:26'),(9,1,7,4,NULL,8,'pending','ecommerce',10.80,0.00,1.58,8.50,0.00,NULL,'Calle Principal 123','Referencia de pago: FlDKK5557141',20.88,20.88,0.00,'USD','2026-02-16 09:41:56','2026-02-16 09:43:14',4,'2026-02-16 09:55:08',4,'2026-02-16 09:41:56','2026-02-16 09:55:08'),(10,1,7,4,NULL,9,'pending','ecommerce',2.60,0.00,0.27,8.50,0.00,NULL,'Calle Principal 123','Referencia de pago: FlDKK55571411',11.37,11.37,0.00,'USD','2026-02-16 09:54:03','2026-02-16 11:02:33',4,'2026-02-16 11:02:33',4,'2026-02-16 09:54:03','2026-02-16 11:02:33');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('i4lx5tIJKmVJpyVG1kO8hzeRM4cJji3D0VN81F8K',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; es-CO) WindowsPowerShell/5.1.26100.7705','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUVOeXdBV3FWYVM0RkhaTnNXTFpXSWtPVmJJY1pXMk9LaGhTRUhIdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770923819),('PCT4wA16sy9NbgUeekodCwq9du83cb2S1DAOYsuC',4,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicENJS3lhakJ2OUVtaXBPRUxOSUZRVzFobWRURG9QeWlFNkRWR0lpUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zZXR0aW5ncyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ7fQ==',1770924578),('uqorAVuIFSN8v1wEhI949kpxAbQ0RXCV8FwMpoS3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVmo1eEw0YVNHTzJPOTdzSzVQdTV4MVJueHhIYlRUZG15VnhEbUF0RyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770950738),('xxPaTuj0EWFYGMLwEKplmZLXjLKluUSobw8lyCCE',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; es-CO) WindowsPowerShell/5.1.26100.7705','YTozOntzOjY6Il90b2tlbiI7czo0MDoibUtSQU96RVF0NU5pd1Z2bFpMVm1ua3JZbldHWDFjNk90anRYMm1PUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770923749);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'business','{\"name\":\"Mi Tienda POS\",\"nit\":\"NIT-000000\",\"address\":\"Calle Principal 123\",\"phone\":\"555-0101\",\"currency\":\"USD\",\"allow_negative_stock\":false,\"default_tax_id\":1,\"payment_qr_url\":\"\\/images\\/payment-qr-sample.svg\"}','2026-02-13 00:29:38','2026-02-16 09:21:11');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxes`
--

DROP TABLE IF EXISTS `taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxes`
--

LOCK TABLES `taxes` WRITE;
/*!40000 ALTER TABLE `taxes` DISABLE KEYS */;
INSERT INTO `taxes` VALUES (1,'IVA 16%',16.00,1,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(2,'Exento 0%',0.00,1,'2026-02-14 21:48:06','2026-02-14 21:48:06');
/*!40000 ALTER TABLE `taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_branch_id_index` (`branch_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,1,'Administrador','ldtapiaposada@gmail.com',NULL,'$2y$12$2BeuFf9QHFrLycaxej743u2lH.uZvDhbg/0vi1lfULus2GC4m/BdS','oDCLQKEWdI1x5xLUSWS9wK7ExHYRDaWv8tm2Qmr7wUUi3dy4OQ39KkfpGakP','2026-02-12 10:08:21','2026-02-17 07:54:04'),(5,1,'Supervisor','supervisor@pos.test',NULL,'$2y$12$cugbkssNeg.A6uLr0EdFiujfOoverpOs9jHPmMlFcZK2fFlF.KT86',NULL,'2026-02-12 10:08:21','2026-02-14 21:48:06'),(6,1,'Cajero','cashier@pos.test',NULL,'$2y$12$lHQSYpiBLI521uUbUZbyB.nLbhgGKocEul5xuulG2ixmsjyoyWGJ.',NULL,'2026-02-12 10:08:22','2026-02-14 21:48:06'),(7,NULL,'Libis marquez','libis@gmail.com',NULL,'$2y$12$t70GLJYYPy8u7xL8vX9iTeqlpqN7fl5cHt3DYvob40Rdlmc9HXnUO',NULL,'2026-02-15 08:02:15','2026-02-15 08:02:15');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'saturnext'
--

--
-- Dumping routines for database 'saturnext'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-17  0:23:01
