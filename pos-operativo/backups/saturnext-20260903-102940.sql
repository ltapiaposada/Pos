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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  UNIQUE KEY `accounting_accounts_company_id_code_unique` (`company_id`,`code`),
  KEY `accounting_accounts_parent_account_id_foreign` (`parent_account_id`),
  KEY `accounting_accounts_company_id_index` (`company_id`),
  CONSTRAINT `accounting_accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_accounts_parent_account_id_foreign` FOREIGN KEY (`parent_account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_accounts`
--

LOCK TABLES `accounting_accounts` WRITE;
/*!40000 ALTER TABLE `accounting_accounts` DISABLE KEYS */;
INSERT INTO `accounting_accounts` VALUES (1,1,'1','Activo','asset','debit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(2,1,'11','Disponible','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(3,1,'1105','Caja','asset','debit',2,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(4,1,'1110','Bancos','asset','debit',2,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(5,1,'13','Deudores','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(6,1,'1305','Clientes','asset','debit',5,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(7,1,'14','Inventarios','asset','debit',1,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(8,1,'1435','Mercancias no fabricadas','asset','debit',7,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(9,1,'2','Pasivo','liability','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(10,1,'22','Proveedores','liability','credit',9,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(11,1,'2205','Nacionales','liability','credit',10,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(12,1,'24','Impuestos por pagar','liability','credit',9,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(13,1,'2408','IVA por pagar','liability','credit',12,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(14,1,'3','Patrimonio','equity','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(15,1,'31','Capital social','equity','credit',14,2,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(16,1,'4','Ingresos','income','credit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(17,1,'41','Ingresos operacionales','income','credit',16,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(18,1,'4135','Comercio al por menor','income','credit',17,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(19,1,'5','Gastos','expense','debit',NULL,1,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(20,1,'51','Administracion','expense','debit',19,2,0,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(21,1,'5105','Gastos de personal','expense','debit',20,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(22,1,'5135','Servicios','expense','debit',20,4,1,1,'2026-02-13 09:01:41','2026-02-13 09:01:41'),(23,1,'6','Costo de ventas','expense','debit',NULL,1,0,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(24,1,'61','Costo de mercancias vendidas','expense','debit',23,2,0,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(25,1,'6135','Comercio al por menor','expense','debit',24,4,1,1,'2026-02-13 09:10:32','2026-02-13 09:10:32'),(26,1,'36','Resultados del ejercicio','equity','credit',14,2,0,1,'2026-02-13 09:22:52','2026-02-13 09:22:52'),(27,1,'3605','Utilidad o perdida del ejercicio','equity','credit',26,4,1,1,'2026-02-13 09:22:52','2026-02-13 09:22:52');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(32) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_company_id_code_unique` (`company_id`,`code`),
  KEY `branches_company_id_name_index` (`company_id`,`name`),
  CONSTRAINT `branches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,1,'Sucursal Principal','PRN','Direccion principal','000-0000','2026-02-12 10:08:21','2026-02-14 21:48:06'),(2,2,'Sucursal Principal','PRN-2',NULL,NULL,'2026-05-05 02:24:48','2026-05-05 02:24:48'),(3,3,'Sucursal Restaurante','REST-01',NULL,NULL,'2026-05-05 04:36:06','2026-05-05 04:36:06'),(4,4,'Sucursal Principal','PRN-4','Calle Principal 123','3214555115','2026-06-01 05:12:46','2026-06-01 05:12:46');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `cash_movements_company_id_index` (`company_id`),
  CONSTRAINT `cash_movements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_movements_cash_register_session_id_foreign` FOREIGN KEY (`cash_register_session_id`) REFERENCES `cash_register_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_movements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_movements`
--

LOCK TABLES `cash_movements` WRITE;
/*!40000 ALTER TABLE `cash_movements` DISABLE KEYS */;
INSERT INTO `cash_movements` VALUES (2,1,2,1,4,'IN',2160.00,'Venta Punto de venta - efectivo','2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,1,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:44:45','2026-02-15 23:44:45'),(4,1,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:44:47','2026-02-15 23:44:47'),(5,1,2,1,4,'IN',232.00,'Venta Punto de venta - efectivo','2026-02-15 23:45:22','2026-02-15 23:45:22'),(6,1,2,1,4,'IN',2160.00,'Venta Punto de venta - efectivo','2026-02-15 23:50:23','2026-02-15 23:50:23'),(7,1,2,1,4,'OUT',5000.00,'Gasto: Compra de camara','2026-02-16 00:05:12','2026-02-16 00:05:12'),(8,1,2,1,4,'IN',2.00,'Venta Punto de venta - efectivo','2026-02-19 07:37:05','2026-02-19 07:37:05'),(9,1,2,1,4,'IN',3.00,'Venta Punto de venta - efectivo','2026-02-19 07:38:44','2026-02-19 07:38:44'),(10,1,4,1,4,'IN',15000.00,'Venta Punto de venta - efectivo','2026-02-24 07:47:16','2026-02-24 07:47:16'),(11,1,4,1,4,'IN',5000.00,'Abono cartera venta #12','2026-02-24 07:50:07','2026-02-24 07:50:07'),(12,1,4,1,4,'IN',2000.00,'Venta Punto de venta - efectivo','2026-06-07 07:07:32','2026-06-07 07:07:32');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `cash_register_sessions_company_id_index` (`company_id`),
  CONSTRAINT `cash_register_sessions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_register_sessions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_register_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_register_sessions`
--

LOCK TABLES `cash_register_sessions` WRITE;
/*!40000 ALTER TABLE `cash_register_sessions` DISABLE KEYS */;
INSERT INTO `cash_register_sessions` VALUES (1,1,1,4,'2026-05-04 19:46:21','2026-02-13 17:08:19',100000.00,1200000.00,100000.00,1100000.00,'closed','2026-02-13 10:12:17','2026-02-13 17:08:19'),(2,1,1,4,'2026-05-04 19:46:21','2026-02-24 07:13:54',100000.00,200000.00,105042.00,94958.00,'closed','2026-02-15 02:15:41','2026-02-24 07:13:54'),(3,1,1,4,'2026-05-04 19:46:21','2026-02-24 07:22:17',0.00,100.00,0.00,100.00,'closed','2026-02-24 07:15:27','2026-02-24 07:22:17'),(4,1,1,4,'2026-06-07 02:10:18','2026-06-07 07:10:18',100000.00,99000.00,144000.00,-45000.00,'closed','2026-02-24 07:24:47','2026-06-07 07:10:18'),(5,2,2,10,'2026-06-07 01:46:22','2026-06-07 06:46:22',1000.00,2000.00,1000.00,1000.00,'closed','2026-05-05 02:55:20','2026-06-07 06:46:22'),(6,3,3,11,'2026-05-05 05:14:16',NULL,100.00,NULL,NULL,NULL,'open','2026-05-05 04:37:26','2026-05-05 05:14:16'),(7,2,2,10,'2026-06-07 06:47:32',NULL,2000.00,NULL,NULL,NULL,'open','2026-06-07 06:47:32','2026-06-07 06:47:32');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  KEY `categories_company_id_index` (`company_id`),
  CONSTRAINT `categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,1,'Mecatos',NULL,NULL,'2026-02-12 10:23:29','2026-02-12 10:23:29'),(2,1,'Bebidas','Bebidas y refrescos',NULL,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(3,1,'Snacks','Snacks y botanas',NULL,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(4,1,'Carnes',NULL,NULL,'2026-02-24 07:30:39','2026-02-24 07:30:39'),(5,2,'Comidas',NULL,NULL,'2026-05-05 03:03:02','2026-05-05 03:03:02'),(6,3,'Menu restaurante','Productos para operacion del modulo restaurante',NULL,'2026-05-05 05:17:25','2026-05-05 05:17:25');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clinical_records`
--

DROP TABLE IF EXISTS `clinical_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `examined_at` datetime NOT NULL,
  `reason_for_consultation` text NOT NULL,
  `medical_history` text DEFAULT NULL,
  `ocular_history` text DEFAULT NULL,
  `examination` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `professional_name` varchar(255) DEFAULT NULL,
  `professional_license` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinical_records_customer_id_foreign` (`customer_id`),
  KEY `clinical_records_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `clinical_records_company_id_customer_id_examined_at_index` (`company_id`,`customer_id`,`examined_at`),
  CONSTRAINT `clinical_records_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clinical_records_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clinical_records_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clinical_records`
--

LOCK TABLES `clinical_records` WRITE;
/*!40000 ALTER TABLE `clinical_records` DISABLE KEYS */;
INSERT INTO `clinical_records` VALUES (1,4,11,15,'2026-06-01 01:49:00','no veo','no','no','no','no','no','no','optica',NULL,'2026-06-01 06:49:40','2026-06-01 06:49:40');
/*!40000 ALTER TABLE `clinical_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `identification` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `company_type_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_domain_unique` (`domain`),
  KEY `companies_company_type_id_foreign` (`company_type_id`),
  KEY `companies_identification_index` (`identification`),
  KEY `companies_email_index` (`email`),
  KEY `companies_status_index` (`status`),
  CONSTRAINT `companies_company_type_id_foreign` FOREIGN KEY (`company_type_id`) REFERENCES `company_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Mi Tienda POS','pos.adanis.com',NULL,NULL,NULL,NULL,3,'active','2026-05-05 00:31:47','2026-05-05 00:31:47'),(2,'Empresa  prueba','','1052022202','ltapiap@gmail.com',NULL,NULL,1,'active','2026-05-05 02:24:48','2026-05-05 02:24:48'),(3,'Restaurante Demo','miposluis.com',NULL,NULL,NULL,NULL,1,'active','2026-05-05 04:36:06','2026-05-05 04:36:06'),(4,'Mi optica','mioptica.com','58552111','mioptica@gmail.com','3214555115','Calle Principal 123',2,'active','2026-06-01 05:12:46','2026-06-01 05:12:46');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_subscriptions`
--

DROP TABLE IF EXISTS `company_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `plan_type` varchar(50) NOT NULL,
  `billing_period` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `payment_status` varchar(30) NOT NULL DEFAULT 'paid',
  `last_payment_date` date DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_subscriptions_company_id_status_end_date_index` (`company_id`,`status`,`end_date`),
  KEY `company_subscriptions_status_index` (`status`),
  KEY `company_subscriptions_payment_status_index` (`payment_status`),
  CONSTRAINT `company_subscriptions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_subscriptions`
--

LOCK TABLES `company_subscriptions` WRITE;
/*!40000 ALTER TABLE `company_subscriptions` DISABLE KEYS */;
INSERT INTO `company_subscriptions` VALUES (1,1,'pos','yearly','2026-05-04','2027-05-04','active','paid','2026-05-04','2027-05-04','2026-05-05 00:31:47','2026-05-05 00:31:47'),(2,2,'pos','yearly','2026-05-04','2027-05-04','cancelled','paid','2026-05-04','2027-05-04','2026-05-05 02:24:49','2026-05-06 12:41:27'),(3,3,'restaurant','monthly','2026-04-29','2026-05-29','active','paid',NULL,NULL,'2026-05-05 04:36:06','2026-05-05 04:36:06'),(4,2,'restaurant','monthly','2026-04-09','2026-05-09','cancelled','paid','2026-05-06','2027-05-06','2026-05-06 12:41:27','2026-05-06 12:47:25'),(5,2,'restaurant','monthly','2026-04-09','2026-05-09','cancelled','paid','2026-04-09','2026-05-05','2026-05-06 12:47:25','2026-06-07 04:59:23'),(6,2,'pos','monthly','2026-04-09','2026-05-09','active','paid','2026-04-09','2026-05-09','2026-05-06 13:34:06','2026-05-06 13:34:06'),(7,4,'optic','monthly','2026-06-01','2026-07-01','active','paid','2026-06-01','2026-07-01','2026-06-01 05:12:46','2026-06-01 05:12:46'),(8,2,'restaurant','monthly','2026-06-02','2026-07-09','active','paid','2026-06-05','2026-07-09','2026-06-07 04:59:23','2026-06-07 04:59:23');
/*!40000 ALTER TABLE `company_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_types`
--

DROP TABLE IF EXISTS `company_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_types_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_types`
--

LOCK TABLES `company_types` WRITE;
/*!40000 ALTER TABLE `company_types` DISABLE KEYS */;
INSERT INTO `company_types` VALUES (1,'Restaurante','restaurant','[\"tables\",\"orders\",\"kitchen\",\"menu\"]',1,'2026-05-05 00:31:47','2026-05-05 00:31:47'),(2,'Óptica','optic','[\"optical_prescriptions\",\"lenses\",\"frames\",\"patients\"]',1,'2026-05-05 00:31:47','2026-05-05 00:31:47'),(3,'POS normal','pos','[\"sales\",\"products\",\"inventory\"]',1,'2026-05-05 00:31:47','2026-05-05 00:31:47');
/*!40000 ALTER TABLE `company_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `customers_company_id_index` (`company_id`),
  CONSTRAINT `customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,1,NULL,'Consumidor final','222222222222',NULL,NULL,NULL,'person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(2,1,NULL,'Cliente Mostrador','CF',NULL,NULL,NULL,'person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(3,1,NULL,'Empresa Demo','NIT-123456','facturas@demo.com','555-0303','Zona Industrial','person',1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(4,1,7,'Libis marquez',NULL,'libis@gmail.com','3214555115','Calle Principal 123','person',1,'2026-02-15 08:02:15','2026-02-15 08:23:45',NULL),(5,1,NULL,'proveedor mas','1112200','elproveedor@gmail.com','3215222141','Direccion principal','supplier',1,'2026-02-16 07:44:12','2026-02-16 07:44:12',NULL),(6,1,NULL,'luis tapia','10520552122','ltapiap@gmail.com','3214555115','Calle Principal 123','person',1,'2026-02-24 07:42:43','2026-02-24 07:42:43',NULL),(7,1,8,'lucho',NULL,'lucho@gmail.com',NULL,NULL,'person',1,'2026-05-02 09:16:02','2026-05-02 09:16:02',NULL),(8,3,NULL,'Libis marquez',NULL,NULL,NULL,NULL,'person',1,'2026-05-06 14:13:49','2026-05-06 14:13:49',NULL),(9,1,13,'Luis torres',NULL,'carnes@gmail.com',NULL,NULL,'person',1,'2026-05-22 09:34:47','2026-05-22 09:34:47',NULL),(10,3,14,'luis torres',NULL,'carne2@gmail.com','3214555115','Calle Principal 123','person',1,'2026-05-22 09:43:26','2026-05-22 09:53:09',NULL),(11,4,NULL,'Libis marquez','1052055212','libis@gmail.com','3214555115',NULL,'person',1,'2026-06-01 06:48:45','2026-06-01 06:48:45',NULL),(12,2,NULL,'Luis David Tapia Posada',NULL,'ltapiap@gmail.com','3016408422','Casa','person',1,'2026-06-07 05:43:24','2026-06-07 05:43:24',NULL),(13,1,16,'Libis marquez',NULL,'luis@gmail.com',NULL,NULL,'person',1,'2026-09-03 09:53:17','2026-09-03 09:53:17',NULL);
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `min_stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_branch_id_product_id_unique` (`branch_id`,`product_id`),
  KEY `inventories_product_id_foreign` (`product_id`),
  KEY `inventories_company_id_index` (`company_id`),
  KEY `inventories_company_id_branch_id_product_id_index` (`company_id`,`branch_id`,`product_id`),
  CONSTRAINT `inventories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` VALUES (1,1,1,1,98.000,10.000,'2026-02-14 21:48:06','2026-02-19 07:38:44'),(2,1,1,2,97.000,10.000,'2026-02-14 21:48:06','2026-06-07 07:07:32'),(3,1,1,3,101.000,10.000,'2026-02-14 21:48:06','2026-02-24 07:54:27'),(4,1,1,4,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(5,1,1,5,100.000,10.000,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(6,1,1,6,94.000,10.000,'2026-02-14 21:48:06','2026-02-24 07:54:26'),(7,1,1,7,30.000,5.000,'2026-02-14 23:59:48','2026-02-14 23:59:48'),(8,1,1,8,28.000,5.000,'2026-02-14 23:59:48','2026-02-16 06:55:27'),(9,1,1,9,29.000,5.000,'2026-02-14 23:59:48','2026-02-15 08:23:46'),(10,1,1,10,26.000,5.000,'2026-02-14 23:59:48','2026-02-16 09:41:56'),(11,1,1,13,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(12,1,1,14,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(13,1,1,15,29.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:23:46'),(14,1,1,16,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(15,1,1,17,30.000,5.000,'2026-02-15 08:06:41','2026-02-15 08:06:41'),(16,1,1,18,13.000,10.000,'2026-02-24 07:41:09','2026-02-24 07:47:16'),(17,3,3,20,30.000,5.000,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(18,3,3,21,45.000,1.000,'2026-05-05 05:17:25','2026-05-06 14:16:10'),(19,3,3,22,25.000,5.000,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(20,3,3,23,50.000,5.000,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(21,3,3,24,60.000,5.000,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(22,3,3,25,45.000,5.000,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(23,3,3,28,5.000,1.000,'2026-05-06 14:16:30','2026-05-06 14:16:30'),(24,3,3,27,6.000,1.000,'2026-05-06 14:18:13','2026-05-06 14:18:13'),(25,2,2,32,5.000,3.000,'2026-06-07 05:05:19','2026-06-07 05:05:19');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_lots`
--

DROP TABLE IF EXISTS `inventory_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `purchase_item_id` bigint(20) unsigned DEFAULT NULL,
  `lot_number` varchar(100) NOT NULL,
  `expires_at` date DEFAULT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `remaining_quantity` decimal(12,3) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_lots_company_id_branch_id_product_id_lot_number_unique` (`company_id`,`branch_id`,`product_id`,`lot_number`),
  KEY `inventory_lots_product_id_foreign` (`product_id`),
  KEY `inventory_lots_purchase_item_id_foreign` (`purchase_item_id`),
  KEY `inventory_lots_branch_id_product_id_expires_at_index` (`branch_id`,`product_id`,`expires_at`),
  CONSTRAINT `inventory_lots_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_lots_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_lots_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_lots_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_lots`
--

LOCK TABLES `inventory_lots` WRITE;
/*!40000 ALTER TABLE `inventory_lots` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `inventory_movements_company_id_index` (`company_id`),
  CONSTRAINT `inventory_movements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
INSERT INTO `inventory_movements` VALUES (2,1,1,2,4,'IN',100.000,800.00,'manual',NULL,NULL,'2026-02-15 02:23:59','2026-02-15 02:23:59'),(3,1,1,2,4,'OUT',100.000,800.00,'manual',NULL,NULL,'2026-02-15 02:25:21','2026-02-15 02:25:21'),(4,1,1,9,7,'OUT',1.000,9.80,'sale',2,'Venta ecommerce','2026-02-15 08:23:46','2026-02-15 08:23:46'),(5,1,1,15,7,'OUT',1.000,10.20,'sale',2,'Venta ecommerce','2026-02-15 08:23:46','2026-02-15 08:23:46'),(6,1,1,2,4,'OUT',1.000,0.40,'sale',3,'Venta Punto de venta','2026-02-15 08:48:15','2026-02-15 08:48:15'),(7,1,1,6,4,'OUT',1.000,0.80,'sale',3,'Venta Punto de venta','2026-02-15 08:48:15','2026-02-15 08:48:15'),(8,1,1,2,4,'IN',1.000,200.00,'purchase',1,'Ingreso por compra','2026-02-15 09:19:09','2026-02-15 09:19:09'),(9,1,1,6,4,'OUT',1.000,0.80,'sale',4,'Venta Punto de venta','2026-02-15 23:44:45','2026-02-15 23:44:45'),(10,1,1,10,4,'OUT',1.000,4.50,'sale',4,'Venta Punto de venta','2026-02-15 23:44:45','2026-02-15 23:44:45'),(11,1,1,6,4,'OUT',1.000,0.80,'sale',5,'Venta Punto de venta','2026-02-15 23:44:47','2026-02-15 23:44:47'),(12,1,1,10,4,'OUT',1.000,4.50,'sale',5,'Venta Punto de venta','2026-02-15 23:44:47','2026-02-15 23:44:47'),(13,1,1,6,4,'OUT',1.000,0.80,'sale',6,'Venta Punto de venta','2026-02-15 23:45:22','2026-02-15 23:45:22'),(14,1,1,10,4,'OUT',1.000,4.50,'sale',6,'Venta Punto de venta','2026-02-15 23:45:22','2026-02-15 23:45:22'),(15,1,1,2,4,'OUT',1.000,200.00,'sale',7,'Venta Punto de venta','2026-02-15 23:50:23','2026-02-15 23:50:23'),(16,1,1,8,4,'OUT',1.000,4.20,'sale',7,'Venta Punto de venta','2026-02-15 23:50:23','2026-02-15 23:50:23'),(17,1,1,6,4,'OUT',1.000,0.80,'sale',8,'Venta Punto de venta','2026-02-16 06:55:26','2026-02-16 06:55:26'),(18,1,1,8,4,'OUT',1.000,4.20,'sale',8,'Venta Punto de venta','2026-02-16 06:55:27','2026-02-16 06:55:27'),(19,1,1,2,4,'IN',1.000,200.00,'purchase',2,'Ingreso por compra','2026-02-16 07:00:07','2026-02-16 07:00:07'),(20,1,1,2,7,'OUT',1.000,200.00,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(21,1,1,6,7,'OUT',1.000,0.80,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(22,1,1,10,7,'OUT',1.000,4.50,'sale',9,'Venta ecommerce','2026-02-16 09:41:56','2026-02-16 09:41:56'),(23,1,1,2,7,'OUT',1.000,200.00,'sale',10,'Venta ecommerce','2026-02-16 09:54:03','2026-02-16 09:54:03'),(24,1,1,6,7,'OUT',1.000,0.80,'sale',10,'Venta ecommerce','2026-02-16 09:54:03','2026-02-16 09:54:03'),(25,1,1,1,4,'OUT',1.000,0.60,'sale',11,'Venta Punto de venta','2026-02-19 07:37:05','2026-02-19 07:37:05'),(26,1,1,1,4,'OUT',1.000,0.60,'sale',12,'Venta Punto de venta','2026-02-19 07:38:44','2026-02-19 07:38:44'),(27,1,1,18,4,'IN',15.000,0.00,'manual',NULL,NULL,'2026-02-24 07:41:09','2026-02-24 07:41:09'),(28,1,1,18,4,'OUT',2.000,10000.00,'sale',13,'Venta Punto de venta','2026-02-24 07:47:16','2026-02-24 07:47:16'),(29,1,1,6,4,'IN',1.000,1000.00,'purchase',3,'Ingreso por compra','2026-02-24 07:54:27','2026-02-24 07:54:27'),(30,1,1,3,4,'IN',1.000,2000.00,'purchase',3,'Ingreso por compra','2026-02-24 07:54:27','2026-02-24 07:54:27'),(31,3,3,21,11,'IN',5.000,0.00,'manual',NULL,NULL,'2026-05-06 14:16:10','2026-05-06 14:16:10'),(32,3,3,28,11,'IN',5.000,0.00,'manual',NULL,NULL,'2026-05-06 14:16:30','2026-05-06 14:16:30'),(33,3,3,27,11,'IN',6.000,0.00,'manual',NULL,NULL,'2026-05-06 14:18:13','2026-05-06 14:18:13'),(34,2,2,32,10,'IN',5.000,0.00,'manual',NULL,NULL,'2026-06-07 05:05:19','2026-06-07 05:05:19'),(37,1,1,2,4,'OUT',1.000,200.00,'sale',16,'Venta Punto de venta (BEB-0002)','2026-06-07 07:07:32','2026-06-07 07:07:32');
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_serials`
--

DROP TABLE IF EXISTS `inventory_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_serials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `purchase_item_id` bigint(20) unsigned DEFAULT NULL,
  `sale_item_id` bigint(20) unsigned DEFAULT NULL,
  `serial_number` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_serials_company_id_product_id_serial_number_unique` (`company_id`,`product_id`,`serial_number`),
  KEY `inventory_serials_product_id_foreign` (`product_id`),
  KEY `inventory_serials_purchase_item_id_foreign` (`purchase_item_id`),
  KEY `inventory_serials_sale_item_id_foreign` (`sale_item_id`),
  KEY `inventory_serials_branch_id_product_id_status_index` (`branch_id`,`product_id`,`status`),
  CONSTRAINT `inventory_serials_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_serials_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_serials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_serials_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_serials_sale_item_id_foreign` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_serials`
--

LOCK TABLES `inventory_serials` WRITE;
/*!40000 ALTER TABLE `inventory_serials` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_serials` ENABLE KEYS */;
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `journal_entries_company_id_index` (`company_id`),
  CONSTRAINT `journal_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `journal_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (2,1,'VS-20260215-0001','2026-02-15','Venta POS #2','posted',4,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,1,'CP-20260215-0001','2026-02-15','Compra #1','posted',4,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(4,1,'VS-20260215-0002','2026-02-15','Venta POS #3','posted',4,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(5,1,'VS-20260215-0003','2026-02-15','Venta POS #4','posted',4,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,1,'VS-20260215-0004','2026-02-15','Venta POS #5','posted',4,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,1,'VS-20260215-0005','2026-02-15','Venta POS #6','posted',4,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,1,'GS-20260215-0001','2026-02-15','Gasto: Compra de camara','posted',4,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(9,1,'VS-20260216-0001','2026-02-16','Venta POS #7','posted',4,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(10,1,'CP-20260216-0001','2026-02-16','Compra #2','posted',4,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(11,1,'PG-20260216-0001','2026-02-16','Pago cartera compra #2','posted',4,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(12,1,'VS-20260216-0002','2026-02-15','Venta POS #1','posted',4,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(13,1,'VS-20260216-0003','2026-02-16','Venta POS #8','posted',4,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(14,1,'VS-20260216-0004','2026-02-16','Venta POS #9','posted',4,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(15,1,'VS-20260216-0005','2026-02-15','Venta POS #1','posted',4,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(16,1,'VS-20260219-0001','2026-02-19','Venta POS #10','posted',4,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(17,1,'RC-20260219-0001','2026-02-19','Recaudo cartera venta #10','posted',4,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(18,1,'GS-20260219-0001','2026-02-19','Gasto: Validacion contable automatica','posted',4,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(19,1,'VS-20260219-0002','2026-02-19','Venta POS #11','posted',4,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(20,1,'RC-20260219-0002','2026-02-19','Recaudo cartera venta #11','posted',4,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(21,1,'GS-20260219-0002','2026-02-19','Gasto: Validacion HTTP modulo contable','posted',4,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(22,1,'VS-20260224-0001','2026-02-24','Venta POS #12','posted',4,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(23,1,'RC-20260224-0001','2026-02-24','Recaudo cartera venta #12','posted',4,'2026-02-24 07:50:07','2026-02-24 07:50:07'),(24,1,'CP-20260224-0001','2026-02-24','Compra #3','posted',4,'2026-02-24 07:54:27','2026-02-24 07:54:27'),(25,NULL,'VS-20260607-0001','2026-06-07','Venta POS #13','posted',4,'2026-06-07 07:07:32','2026-06-07 07:07:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entry_lines`
--

LOCK TABLES `journal_entry_lines` WRITE;
/*!40000 ALTER TABLE `journal_entry_lines` DISABLE KEYS */;
INSERT INTO `journal_entry_lines` VALUES (2,2,3,'Ingreso por venta - efectivo',2160.00,0.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(3,2,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,2,13,'IVA generado en venta',0.00,160.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(5,2,25,'Costo de venta',1.20,0.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(6,2,8,'Salida de inventario por venta',0.00,1.20,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(7,3,8,'Ingreso de inventario por compra',200.00,0.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(8,3,3,'Pago compra en efectivo',0.00,200.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(9,4,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(10,4,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(11,4,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(12,4,25,'Costo de venta',5.30,0.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(13,4,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(14,5,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(15,5,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(16,5,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(17,5,25,'Costo de venta',5.30,0.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(18,5,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(19,6,3,'Ingreso por venta - efectivo',232.00,0.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(20,6,18,'Ingreso operativo por venta',0.00,200.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(21,6,13,'IVA generado en venta',0.00,32.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(22,6,25,'Costo de venta',5.30,0.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(23,6,8,'Salida de inventario por venta',0.00,5.30,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(24,7,3,'Ingreso por venta - efectivo',2160.00,0.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(25,7,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(26,7,13,'IVA generado en venta',0.00,160.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(27,7,25,'Costo de venta',204.20,0.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(28,7,8,'Salida de inventario por venta',0.00,204.20,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(29,8,21,'Registro gasto: Compra de camara',5000.00,0.00,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(30,8,3,'Salida por gasto - caja',0.00,5000.00,'2026-02-16 00:05:11','2026-02-16 00:05:11'),(31,9,6,'Cuenta por cobrar venta',2320.00,0.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(32,9,18,'Ingreso operativo por venta',0.00,2000.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(33,9,13,'IVA generado en venta',0.00,320.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(34,9,25,'Costo de venta',5.00,0.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(35,9,8,'Salida de inventario por venta',0.00,5.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(36,10,8,'Ingreso de inventario por compra',200.00,0.00,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(37,10,11,'Cuenta por pagar proveedor',0.00,200.00,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(38,11,11,'Disminucion cuenta por pagar',100.00,0.00,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(39,11,4,'Pago cartera proveedor',0.00,100.00,'2026-02-16 07:01:20','2026-02-16 07:01:20'),(40,12,4,'Ingreso por venta - bancos',50.84,0.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(41,12,18,'Ingreso operativo por venta',0.00,45.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(42,12,13,'IVA generado en venta',0.00,5.84,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(43,12,25,'Costo de venta',20.00,0.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(44,12,8,'Salida de inventario por venta',0.00,20.00,'2026-02-16 09:49:06','2026-02-16 09:49:06'),(45,13,6,'Cuenta por cobrar venta',20.88,0.00,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(46,13,18,'Ingreso operativo por venta',0.00,19.30,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(47,13,13,'IVA generado en venta',0.00,1.58,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(48,13,25,'Costo de venta',205.30,0.00,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(49,13,8,'Salida de inventario por venta',0.00,205.30,'2026-02-16 09:55:08','2026-02-16 09:55:08'),(50,14,6,'Cuenta por cobrar venta',11.37,0.00,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(51,14,18,'Ingreso operativo por venta',0.00,11.10,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(52,14,13,'IVA generado en venta',0.00,0.27,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(53,14,25,'Costo de venta',200.80,0.00,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(54,14,8,'Salida de inventario por venta',0.00,200.80,'2026-02-16 11:02:33','2026-02-16 11:02:33'),(55,15,4,'Ingreso por venta - bancos',50.84,0.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(56,15,18,'Ingreso operativo por venta',0.00,45.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(57,15,13,'IVA generado en venta',0.00,5.84,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(58,15,25,'Costo de venta',20.00,0.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(59,15,8,'Salida de inventario por venta',0.00,20.00,'2026-02-16 11:02:44','2026-02-16 11:02:44'),(60,16,3,'Ingreso por venta - efectivo',2.00,0.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(61,16,6,'Cuenta por cobrar venta',56.00,0.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(62,16,18,'Ingreso operativo por venta',0.00,50.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(63,16,13,'IVA generado en venta',0.00,8.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(64,16,25,'Costo de venta',0.60,0.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(65,16,8,'Salida de inventario por venta',0.00,0.60,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(66,17,4,'Recaudo cartera venta',5.00,0.00,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(67,17,6,'Disminucion cuenta por cobrar',0.00,5.00,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(68,18,21,'Registro gasto: Validacion contable automatica',12.50,0.00,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(69,18,4,'Salida por gasto - banco',0.00,12.50,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(70,19,3,'Ingreso por venta - efectivo',3.00,0.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(71,19,6,'Cuenta por cobrar venta',43.40,0.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(72,19,18,'Ingreso operativo por venta',0.00,40.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(73,19,13,'IVA generado en venta',0.00,6.40,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(74,19,25,'Costo de venta',0.60,0.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(75,19,8,'Salida de inventario por venta',0.00,0.60,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(76,20,4,'Recaudo cartera venta',4.00,0.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(77,20,6,'Disminucion cuenta por cobrar',0.00,4.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(78,21,21,'Registro gasto: Validacion HTTP modulo contable',9.25,0.00,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(79,21,4,'Salida por gasto - banco',0.00,9.25,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(80,22,3,'Ingreso por venta - efectivo',15000.00,0.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(81,22,6,'Cuenta por cobrar venta',15000.00,0.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(82,22,18,'Ingreso operativo por venta',0.00,30000.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(83,22,25,'Costo de venta',20000.00,0.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(84,22,8,'Salida de inventario por venta',0.00,20000.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(85,23,3,'Recaudo cartera venta',5000.00,0.00,'2026-02-24 07:50:07','2026-02-24 07:50:07'),(86,23,6,'Disminucion cuenta por cobrar',0.00,5000.00,'2026-02-24 07:50:07','2026-02-24 07:50:07'),(87,24,8,'Ingreso de inventario por compra',3480.00,0.00,'2026-02-24 07:54:27','2026-02-24 07:54:27'),(88,24,11,'Cuenta por pagar proveedor',0.00,3480.00,'2026-02-24 07:54:27','2026-02-24 07:54:27'),(89,25,3,'Ingreso por venta - efectivo',2000.00,0.00,'2026-06-07 07:07:32','2026-06-07 07:07:32'),(90,25,18,'Ingreso operativo por venta',0.00,2000.00,'2026-06-07 07:07:32','2026-06-07 07:07:32'),(91,25,25,'Costo de venta',200.00,0.00,'2026-06-07 07:07:32','2026-06-07 07:07:32'),(92,25,8,'Salida de inventario por venta',0.00,200.00,'2026-06-07 07:07:32','2026-06-07 07:07:32');
/*!40000 ALTER TABLE `journal_entry_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_orders`
--

DROP TABLE IF EXISTS `medical_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `clinical_record_id` bigint(20) unsigned DEFAULT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `ordered_at` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `prescription_details` text NOT NULL,
  `usage_instructions` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `professional_name` varchar(255) DEFAULT NULL,
  `professional_license` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medical_orders_customer_id_foreign` (`customer_id`),
  KEY `medical_orders_clinical_record_id_foreign` (`clinical_record_id`),
  KEY `medical_orders_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `medical_orders_company_id_customer_id_status_index` (`company_id`,`customer_id`,`status`),
  CONSTRAINT `medical_orders_clinical_record_id_foreign` FOREIGN KEY (`clinical_record_id`) REFERENCES `clinical_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_orders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_orders_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medical_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_orders`
--

LOCK TABLES `medical_orders` WRITE;
/*!40000 ALTER TABLE `medical_orders` DISABLE KEYS */;
INSERT INTO `medical_orders` VALUES (1,4,11,1,15,'2026-06-01 01:50:00','active','mi pormuladdd','ddd','dddd','optica',NULL,'2026-06-01 06:51:10','2026-06-01 06:51:56');
/*!40000 ALTER TABLE `medical_orders` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_11_034320_create_permission_tables',1),(5,'2026_02_11_100000_create_branches_table',1),(6,'2026_02_11_100010_create_categories_table',1),(7,'2026_02_11_100020_create_taxes_table',1),(8,'2026_02_11_100030_create_customers_table',1),(9,'2026_02_11_100040_create_products_table',1),(10,'2026_02_11_100050_create_inventories_table',1),(11,'2026_02_11_100060_create_inventory_movements_table',1),(12,'2026_02_11_100065_create_cash_register_sessions_table',1),(13,'2026_02_11_100070_create_sales_table',1),(14,'2026_02_11_100080_create_sale_items_table',1),(15,'2026_02_11_100081_create_payments_table',1),(16,'2026_02_11_100090_create_cash_movements_table',1),(17,'2026_02_11_100100_create_settings_table',1),(18,'2026_02_11_100110_create_returns_table',1),(19,'2026_02_11_100120_create_return_items_table',1),(20,'2026_02_11_100130_add_branch_id_to_users_table',1),(21,'2026_02_13_032500_add_product_types_and_kit_items',2),(22,'2026_02_13_040000_create_accounting_tables',3),(23,'2026_02_13_041000_add_puc_fields_to_accounting_accounts',4),(24,'2026_02_13_043000_create_accounting_period_closures_table',5),(25,'2026_02_14_120000_add_user_id_to_customers_table',6),(26,'2026_02_14_130000_add_ecommerce_fields_to_sales_table',6),(27,'2026_02_14_131000_add_image_url_to_products_table',6),(28,'2026_02_14_132000_add_is_visible_ecommerce_to_products_table',7),(29,'2026_02_15_040000_create_purchases_tables',8),(30,'2026_02_15_180000_create_purchase_payments_table',9),(31,'2026_02_16_120000_add_contact_type_to_customers_table',10),(32,'2026_02_16_120000_add_void_fields_to_credit_payments',11),(33,'2026_02_16_123000_add_invoice_fields_to_sales_table',12),(34,'2026_02_16_130000_add_accounting_fields_to_sales_table',13),(35,'2026_05_04_120000_create_software_licenses_tables',14),(36,'2026_05_04_130000_add_platform_owner_and_customer_id_to_users_table',14),(37,'2026_05_04_140000_add_is_customer_owner_to_users_table',14),(38,'2026_05_04_140000_create_company_types_table',15),(39,'2026_05_04_140100_create_companies_table',15),(40,'2026_05_04_140200_create_company_subscriptions_table',15),(41,'2026_05_04_140300_add_company_context_to_core_tables',15),(42,'2026_05_04_150000_add_company_id_to_operational_tables',16),(43,'2026_05_04_160000_create_restaurant_tables_module',17),(44,'2026_05_04_170000_create_product_modifier_tables',18),(45,'2026_05_05_090000_add_inventory_fields_to_product_modifiers',19),(46,'2026_05_06_090000_add_unit_conversion_to_product_kit_items',20),(47,'2026_05_21_090000_add_domain_to_companies_table',21),(48,'2026_05_31_170000_create_optometry_module_tables',22),(49,'2026_05_31_171000_seed_optometry_permissions',22),(50,'2026_06_05_120000_add_extended_product_types_and_inventory_tracking',23),(51,'2026_06_07_000000_add_uses_component_groups_to_products',24),(52,'2026_06_28_010000_add_variant_attributes_to_products_table',25),(53,'2026_06_29_010000_create_product_variant_attribute_tables',26);
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',9),(1,'App\\Models\\User',10),(1,'App\\Models\\User',11),(1,'App\\Models\\User',12),(1,'App\\Models\\User',15),(2,'App\\Models\\User',5),(3,'App\\Models\\User',6),(4,'App\\Models\\User',7),(4,'App\\Models\\User',8),(4,'App\\Models\\User',13),(4,'App\\Models\\User',14),(4,'App\\Models\\User',16),(5,'App\\Models\\User',4);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `optometry_patient_profiles`
--

DROP TABLE IF EXISTS `optometry_patient_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `optometry_patient_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(32) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `systemic_history` text DEFAULT NULL,
  `ocular_history` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `optometry_patient_profiles_company_id_customer_id_unique` (`company_id`,`customer_id`),
  KEY `optometry_patient_profiles_customer_id_foreign` (`customer_id`),
  CONSTRAINT `optometry_patient_profiles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `optometry_patient_profiles_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `optometry_patient_profiles`
--

LOCK TABLES `optometry_patient_profiles` WRITE;
/*!40000 ALTER TABLE `optometry_patient_profiles` DISABLE KEYS */;
INSERT INTO `optometry_patient_profiles` VALUES (1,4,11,'1994-02-25','female',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-01 06:48:45','2026-06-01 06:48:45');
/*!40000 ALTER TABLE `optometry_patient_profiles` ENABLE KEYS */;
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
INSERT INTO `password_reset_tokens` VALUES ('admin@pos.test','$2y$12$eLFwTftPRsREeIJapiJ86eBbBGWHgVedwr8UPlurPh42QTU.1qn/2','2026-02-17 06:42:37'),('ldtapiaposada@gmail.com','$2y$12$.7KVS1kdH5O/NVxXWtv2yuap0cfpJO7KOlCvitdocqF6aYW3b/81a','2026-05-02 08:32:03');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `payments_company_id_index` (`company_id`),
  CONSTRAINT `payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_voided_by_user_id_foreign` FOREIGN KEY (`voided_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,1,2,'card',50.84,'E-COMMERCE','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 08:23:46','2026-02-15 08:23:46'),(3,1,3,'cash',2160.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,1,4,'cash',232.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(5,1,5,'cash',232.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,1,6,'cash',232.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,1,7,'cash',2160.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,1,8,'credit',2320.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(9,1,9,'qr',20.88,'FlDKK5557141','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(10,1,10,'qr',11.37,'FlDKK55571411','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-16 09:54:03','2026-02-16 09:54:03'),(11,1,11,'cash',2.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(12,1,11,'credit',56.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(13,1,11,'transfer',5.00,'VALID-CLI','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:37:06','2026-02-19 07:37:06'),(14,1,12,'cash',3.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(15,1,12,'credit',43.40,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(16,1,12,'transfer',4.00,'HTTP-VALID','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(17,1,13,'cash',15000.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(18,1,13,'credit',15000.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(19,1,13,'cash',5000.00,NULL,'2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-24 07:50:07','2026-02-24 07:50:07'),(22,1,16,'cash',2000.00,NULL,'2026-06-07 07:07:32',NULL,NULL,NULL,'2026-06-07 07:07:32','2026-06-07 07:07:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage_users','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(2,'manage_settings','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(3,'manage_products','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(4,'manage_categories','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(5,'manage_customers','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(6,'manage_inventory','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(7,'view_reports','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(8,'open_cash_register','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(9,'close_cash_register','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(10,'record_cash_movement','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(11,'create_sale','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(12,'apply_discount','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(13,'apply_high_discount','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(14,'void_sale','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(15,'process_return','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(16,'manage_accounting','web','2026-02-13 09:01:26','2026-02-13 09:01:26'),(17,'manage_branches','web','2026-02-13 09:43:09','2026-02-13 09:43:09'),(18,'manage_ecommerce_orders','web','2026-02-14 21:48:05','2026-02-14 21:48:05'),(19,'manage_purchases','web','2026-02-15 09:01:12','2026-02-15 09:01:12'),(20,'manage_companies','web','2026-05-05 00:36:14','2026-05-05 00:36:14'),(21,'manage_subscriptions','web','2026-05-05 00:36:14','2026-05-05 00:36:14'),(22,'manage_restaurant','web','2026-05-05 04:07:55','2026-05-05 04:07:55'),(23,'manage_restaurant_kitchen','web','2026-05-05 04:07:55','2026-05-05 04:07:55'),(24,'manage_optometry_patients','web','2026-06-01 05:18:56','2026-06-01 05:18:56'),(25,'manage_optometry_records','web','2026-06-01 05:18:56','2026-06-01 05:18:56'),(26,'manage_optometry_orders','web','2026-06-01 05:18:56','2026-06-01 05:18:56');
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
  `component_unit` varchar(32) DEFAULT NULL,
  `component_unit_factor` decimal(12,6) NOT NULL DEFAULT 1.000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_kit_items_kit_product_id_component_product_id_unique` (`kit_product_id`,`component_product_id`),
  KEY `product_kit_items_component_product_id_foreign` (`component_product_id`),
  CONSTRAINT `product_kit_items_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_kit_items_kit_product_id_foreign` FOREIGN KEY (`kit_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_kit_items`
--

LOCK TABLES `product_kit_items` WRITE;
/*!40000 ALTER TABLE `product_kit_items` DISABLE KEYS */;
INSERT INTO `product_kit_items` VALUES (14,11,2,1.000,NULL,1.000000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(15,11,1,1.000,NULL,1.000000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(16,12,5,1.000,NULL,1.000000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(17,12,6,1.000,NULL,1.000000,'2026-02-15 08:11:43','2026-02-15 08:11:43'),(18,31,30,250.000,'g',0.002000,'2026-06-07 04:46:00','2026-06-07 04:46:00');
/*!40000 ALTER TABLE `product_kit_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_groups`
--

DROP TABLE IF EXISTS `product_modifier_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_modifier_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `selection_type` varchar(20) NOT NULL DEFAULT 'single',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `min_select` int(10) unsigned NOT NULL DEFAULT 0,
  `max_select` int(10) unsigned NOT NULL DEFAULT 1,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_modifier_groups_product_id_foreign` (`product_id`),
  KEY `product_modifier_groups_company_id_product_id_index` (`company_id`,`product_id`),
  CONSTRAINT `product_modifier_groups_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_modifier_groups_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_groups`
--

LOCK TABLES `product_modifier_groups` WRITE;
/*!40000 ALTER TABLE `product_modifier_groups` DISABLE KEYS */;
INSERT INTO `product_modifier_groups` VALUES (1,3,20,'Proteina','single',1,1,1,1,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(2,3,20,'Extras','multiple',0,0,3,2,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(3,3,20,'Quitar ingredientes','remove',0,0,0,3,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(7,3,23,'Salsas','multiple',0,0,2,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(8,3,24,'Azucar','single',1,1,1,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(9,3,25,'Sabor','single',1,1,1,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(10,3,28,'Proteina','multiple',0,0,1,0,'2026-05-06 14:09:10','2026-05-06 14:09:10'),(11,3,21,'Proteina','single',1,1,1,0,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(12,3,21,'Acompanantes','multiple',1,1,3,1,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(13,3,21,'Quitar ingredientes','remove',0,0,0,2,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(17,2,33,'Tips','multiple',1,0,1,0,'2026-06-07 05:33:51','2026-06-07 05:33:51');
/*!40000 ALTER TABLE `product_modifier_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_options`
--

DROP TABLE IF EXISTS `product_modifier_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_modifier_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `product_modifier_group_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `inventory_quantity` decimal(12,3) DEFAULT NULL,
  `inventory_unit` varchar(32) DEFAULT NULL,
  `inventory_unit_factor` decimal(12,6) NOT NULL DEFAULT 1.000000,
  `label` varchar(255) NOT NULL,
  `price_delta` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_modifier_options_company_id_foreign` (`company_id`),
  KEY `product_modifier_options_product_modifier_group_id_foreign` (`product_modifier_group_id`),
  KEY `product_modifier_options_product_id_foreign` (`product_id`),
  CONSTRAINT `product_modifier_options_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_modifier_options_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_modifier_options_product_modifier_group_id_foreign` FOREIGN KEY (`product_modifier_group_id`) REFERENCES `product_modifier_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_options`
--

LOCK TABLES `product_modifier_options` WRITE;
/*!40000 ALTER TABLE `product_modifier_options` DISABLE KEYS */;
INSERT INTO `product_modifier_options` VALUES (1,3,1,NULL,NULL,NULL,1.000000,'Carne',0.00,1,1,1,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(2,3,1,NULL,NULL,NULL,1.000000,'Pollo crispy',2000.00,0,1,2,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(3,3,1,NULL,NULL,NULL,1.000000,'Mixta',3000.00,0,1,3,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(4,3,2,NULL,NULL,NULL,1.000000,'Queso cheddar',1500.00,0,1,1,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(5,3,2,NULL,NULL,NULL,1.000000,'Tocineta',2500.00,0,1,2,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(6,3,2,NULL,NULL,NULL,1.000000,'Huevo frito',2000.00,0,1,3,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(7,3,3,NULL,NULL,NULL,1.000000,'Cebolla',0.00,1,1,1,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(8,3,3,NULL,NULL,NULL,1.000000,'Tomate',0.00,1,1,2,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(9,3,3,NULL,NULL,NULL,1.000000,'Lechuga',0.00,1,1,3,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(10,3,3,NULL,NULL,NULL,1.000000,'Salsa especial',0.00,1,1,4,'2026-05-05 05:17:25','2026-05-05 05:17:25'),(22,3,7,NULL,NULL,NULL,1.000000,'Salsa de tomate',0.00,0,1,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(23,3,7,NULL,NULL,NULL,1.000000,'Mayonesa',0.00,0,1,2,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(24,3,7,NULL,NULL,NULL,1.000000,'Queso cheddar',1200.00,0,1,3,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(25,3,8,NULL,NULL,NULL,1.000000,'Normal',0.00,1,1,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(26,3,8,NULL,NULL,NULL,1.000000,'Sin azucar',0.00,0,1,2,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(27,3,8,NULL,NULL,NULL,1.000000,'Endulzante',500.00,0,1,3,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(28,3,9,NULL,NULL,NULL,1.000000,'Mora',0.00,1,1,1,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(29,3,9,NULL,NULL,NULL,1.000000,'Maracuya',0.00,0,1,2,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(30,3,9,NULL,NULL,NULL,1.000000,'Mango',0.00,0,1,3,'2026-05-05 05:17:26','2026-05-05 05:17:26'),(31,3,10,26,1.000,'libra',300.000000,'Carne de res',0.00,0,1,0,'2026-05-06 14:09:11','2026-05-06 14:09:11'),(32,3,10,27,1.000,'libra',300.000000,'Pollo',0.00,1,1,1,'2026-05-06 14:09:11','2026-05-06 14:09:11'),(33,3,11,NULL,1.000,NULL,1.000000,'Carne',0.00,1,1,0,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(34,3,11,NULL,1.000,NULL,1.000000,'Cerdo',0.00,0,1,1,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(35,3,11,NULL,1.000,NULL,1.000000,'Pechuga',1500.00,0,1,2,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(36,3,11,NULL,1.000,NULL,1.000000,'Pescado',3000.00,0,1,3,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(37,3,12,NULL,1.000,NULL,1.000000,'Arroz',0.00,1,1,0,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(38,3,12,NULL,1.000,NULL,1.000000,'Ensalada',0.00,1,1,1,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(39,3,12,NULL,1.000,NULL,1.000000,'Papa frita',1000.00,0,1,2,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(40,3,12,NULL,1.000,NULL,1.000000,'Frijol',0.00,0,1,3,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(41,3,13,NULL,1.000,NULL,1.000000,'Cebolla',0.00,1,1,0,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(42,3,13,NULL,1.000,NULL,1.000000,'Ensalada',0.00,1,1,1,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(43,3,13,NULL,1.000,NULL,1.000000,'Aji',0.00,1,1,2,'2026-05-22 09:25:42','2026-05-22 09:25:42'),(47,2,17,32,250.000,'g',0.002000,'mi compuesto',0.00,1,1,0,'2026-06-07 05:33:51','2026-06-07 05:33:51');
/*!40000 ALTER TABLE `product_modifier_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variant_attribute_values`
--

DROP TABLE IF EXISTS `product_variant_attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variant_attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_variant_attribute_id` bigint(20) unsigned NOT NULL,
  `value` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variant_attribute_values_unique` (`product_variant_attribute_id`,`value`),
  CONSTRAINT `variant_attr_values_attr_fk` FOREIGN KEY (`product_variant_attribute_id`) REFERENCES `product_variant_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variant_attribute_values`
--

LOCK TABLES `product_variant_attribute_values` WRITE;
/*!40000 ALTER TABLE `product_variant_attribute_values` DISABLE KEYS */;
INSERT INTO `product_variant_attribute_values` VALUES (1,1,'L','2026-06-29 10:21:29','2026-06-29 10:21:29'),(2,1,'M','2026-06-29 10:21:29','2026-06-29 10:21:29'),(3,1,'S','2026-06-29 10:21:29','2026-06-29 10:21:29'),(4,2,'Rojo','2026-06-29 10:23:38','2026-06-29 10:23:38'),(5,2,'Azul','2026-06-29 10:23:38','2026-06-29 10:23:38');
/*!40000 ALTER TABLE `product_variant_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variant_attributes`
--

DROP TABLE IF EXISTS `product_variant_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variant_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variant_attributes_company_id_name_unique` (`company_id`,`name`),
  CONSTRAINT `product_variant_attributes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variant_attributes`
--

LOCK TABLES `product_variant_attributes` WRITE;
/*!40000 ALTER TABLE `product_variant_attributes` DISABLE KEYS */;
INSERT INTO `product_variant_attributes` VALUES (1,1,'Talla','2026-06-29 10:21:29','2026-06-29 10:21:29'),(2,1,'Color','2026-06-29 10:23:38','2026-06-29 10:23:38');
/*!40000 ALTER TABLE `product_variant_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `tax_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `unit` varchar(32) NOT NULL DEFAULT 'unit',
  `product_type` varchar(16) NOT NULL DEFAULT 'simple',
  `uses_component_groups` tinyint(1) NOT NULL DEFAULT 0,
  `parent_product_id` bigint(20) unsigned DEFAULT NULL,
  `variant_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_attributes`)),
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible_ecommerce` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_company_id_sku_unique` (`company_id`,`sku`),
  UNIQUE KEY `products_company_id_barcode_unique` (`company_id`,`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_tax_id_foreign` (`tax_id`),
  KEY `products_parent_product_id_foreign` (`parent_product_id`),
  KEY `products_company_id_index` (`company_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_parent_product_id_foreign` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,2,1,'Refresco Cola 600ml','BEB-0001','7501000000011','/images/products/cola.svg','Bebida gaseosa sabor cola',NULL,'unit','simple',0,NULL,NULL,0.60,1.20,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(2,1,2,2,'Agua 600ml','BEB-0002','7702184580479','/images/products/agua.svg','Agua purificada sin gas',NULL,'unit','simple',0,NULL,NULL,200.00,0.90,1,1,'2026-02-14 21:48:06','2026-06-06 22:01:59',NULL),(3,1,2,1,'Cafe Molido Premium 250g','BEB-0003','7501000000035','/images/products/cafe.svg','Cafe tostado molido premium',NULL,'unit','simple',0,NULL,NULL,2000.00,4.80,1,1,'2026-02-14 21:48:06','2026-02-24 07:54:27',NULL),(4,1,3,1,'Papas Fritas 150g','SNK-0001','7501000000042','/images/products/papas.svg','Snack salado crujiente',NULL,'unit','simple',0,NULL,NULL,0.70,1.50,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(5,1,3,1,'Galletas Chocolate 120g','SNK-0002','7501000000059','/images/products/galletas.svg','Galletas rellenas de chocolate',NULL,'unit','simple',0,NULL,NULL,0.90,1.90,1,1,'2026-02-14 21:48:06','2026-02-14 21:48:06',NULL),(6,1,3,1,'Barra de Chocolate 90g','SNK-0003','7501000000066','/images/products/chocolate.svg','Chocolate semiamargo',NULL,'unit','simple',0,NULL,NULL,1000.00,1.70,1,1,'2026-02-14 21:48:06','2026-02-24 07:54:27',NULL),(7,1,3,1,'Camiseta deportiva base','BAS-TSHIRT','7501000001001','/images/product-placeholder.svg','Producto base para variantes de talla.',NULL,'unit','simple',0,NULL,NULL,9.50,16.00,1,0,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(8,1,2,1,'Cafe base origen','BAS-COFFEE','7501000001002','/images/products/cafe.svg','Producto base para variantes de molienda.',NULL,'unit','simple',0,NULL,NULL,4.20,7.50,1,0,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(9,1,3,1,'Camiseta deportiva talla M','VAR-TSHIRT-M','7501000001011','/images/product-placeholder.svg','Variante talla M.',NULL,'unit','variant',0,7,NULL,9.80,17.90,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(10,1,2,1,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012','/images/products/cafe.svg','Variante en grano.',NULL,'unit','variant',0,8,NULL,4.50,8.20,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(11,1,2,1,'Kit hidratacion','KIT-HIDRA','7501000001021','/images/products/agua.svg','Incluye 1 agua + 1 refresco.',NULL,'unit','kit',0,NULL,NULL,0.00,1.95,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(12,1,3,1,'Kit snack dulce','KIT-SNACK','7501000001022','/images/products/galletas.svg','Incluye galletas + barra de chocolate.',NULL,'unit','kit',0,NULL,NULL,0.00,3.30,1,1,'2026-02-14 23:59:48','2026-02-14 23:59:48',NULL),(13,1,3,1,'Camiseta deportiva talla S','VAR-TSHIRT-S','7501000001010','/images/product-placeholder.svg','Variante talla S.',NULL,'unit','variant',0,7,NULL,9.60,17.50,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(14,1,3,1,'Camiseta deportiva talla L','VAR-TSHIRT-L','7501000001013','/images/product-placeholder.svg','Variante talla L.',NULL,'unit','variant',0,7,NULL,10.00,18.20,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(15,1,3,1,'Camiseta deportiva talla XL','VAR-TSHIRT-XL','7501000001014','/images/product-placeholder.svg','Variante talla XL.',NULL,'unit','variant',0,7,NULL,10.20,18.60,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(16,1,2,1,'Cafe origen molido 500g','VAR-COFFEE-MOLIDO','7501000001015','/images/products/cafe.svg','Variante molido tradicional.',NULL,'unit','variant',0,8,NULL,4.40,8.10,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(17,1,2,1,'Cafe descafeinado 500g','VAR-COFFEE-DESCAFEINADO','7501000001016','/images/products/cafe.svg','Variante descafeinado.',NULL,'unit','variant',0,8,NULL,4.70,8.60,1,1,'2026-02-15 08:06:41','2026-02-15 08:06:41',NULL),(18,1,4,NULL,'Carne de res','CARRES',NULL,NULL,NULL,NULL,'unit','simple',0,NULL,NULL,10000.00,15000.00,1,1,'2026-02-24 07:39:34','2026-02-24 07:39:34',NULL),(19,1,2,NULL,'Producto con imagen','N42255','N42255','https://pub-f655c8961ea043dba809216aad023e1b.r2.dev/Pos/2026/05/09133892-ee0a-47c2-a98b-68d767a81cc1.jpg',NULL,NULL,'unit','simple',0,NULL,NULL,0.00,0.00,1,1,'2026-05-02 08:27:50','2026-05-02 08:27:50',NULL),(20,3,6,NULL,'Hamburguesa clasica','REST-BURG-001',NULL,NULL,'Hamburguesa para practicas del modulo restaurante',NULL,'plato','simple',0,NULL,NULL,9000.00,18000.00,1,0,'2026-05-05 05:17:25','2026-05-05 05:17:25',NULL),(21,3,6,NULL,'Almuerzo corriente','REST-ALM-001',NULL,NULL,'Almuerzo configurable para practicar pedidos por mesa',NULL,'plato','simple',0,NULL,NULL,8000.00,16000.00,1,1,'2026-05-05 05:17:25','2026-05-22 09:25:42',NULL),(22,3,6,NULL,'Pollo apanado con papas','REST-POL-001',NULL,NULL,'Plato fuerte para practicas restaurante',NULL,'plato','simple',0,NULL,NULL,9500.00,19500.00,1,0,'2026-05-05 05:17:26','2026-05-05 05:17:26',NULL),(23,3,6,NULL,'Papas a la francesa','REST-PAP-001',NULL,NULL,'Acompanante o entrada',NULL,'porcion','simple',0,NULL,NULL,2500.00,7000.00,1,0,'2026-05-05 05:17:26','2026-05-05 05:17:26',NULL),(24,3,6,NULL,'Limonada natural','REST-LIM-001',NULL,NULL,'Bebida fria para practicas restaurante',NULL,'vaso','simple',0,NULL,NULL,1800.00,5500.00,1,0,'2026-05-05 05:17:26','2026-05-05 05:17:26',NULL),(25,3,6,NULL,'Jugo del dia','REST-JUG-001',NULL,NULL,'Bebida fresca para practicas restaurante',NULL,'vaso','simple',0,NULL,NULL,2000.00,6000.00,1,0,'2026-05-05 05:17:26','2026-05-05 05:17:26',NULL),(26,3,NULL,NULL,'Carne de res','CARRES',NULL,NULL,NULL,NULL,'libra','simple',0,NULL,NULL,1000.00,2000.00,1,1,'2026-05-06 14:05:06','2026-05-06 14:05:06',NULL),(27,3,NULL,NULL,'Pollo','poll',NULL,NULL,NULL,NULL,'libra','simple',0,NULL,NULL,1000.00,2000.00,1,1,'2026-05-06 14:06:22','2026-05-06 14:06:22',NULL),(28,3,NULL,NULL,'Comidas','Comer',NULL,NULL,NULL,NULL,'unit','simple',0,NULL,NULL,10000.00,20000.00,1,1,'2026-05-06 14:09:10','2026-05-06 14:09:10',NULL),(29,NULL,4,NULL,'template','BEB-2502','4702194580479',NULL,NULL,NULL,'libra','simple',0,NULL,NULL,1000.00,2000.00,1,1,'2026-06-07 04:11:17','2026-06-07 04:12:05',NULL),(30,1,4,NULL,'mi compuesto','BEB-0102',NULL,NULL,NULL,NULL,'libra','simple',0,NULL,NULL,1000.00,2000.00,1,1,'2026-06-07 04:43:36','2026-06-07 04:43:36',NULL),(31,1,3,NULL,'mi template','BEB-2002',NULL,NULL,NULL,NULL,'unit','kit',0,NULL,NULL,1000.00,25000.00,1,1,'2026-06-07 04:46:00','2026-06-07 04:46:00',NULL),(32,2,5,NULL,'mi compuesto','BEB-0102','4702194580479',NULL,NULL,NULL,'libra','simple',0,NULL,NULL,1000.00,2000.00,1,1,'2026-06-07 05:02:23','2026-06-07 05:02:23',NULL),(33,2,5,NULL,'mi template','BEB-2502','7702184580479',NULL,NULL,NULL,'unit','kit',1,NULL,NULL,10000.00,12000.00,1,1,'2026-06-07 05:04:03','2026-06-07 05:33:51',NULL),(34,1,NULL,NULL,'Camisa','CAM',NULL,NULL,NULL,NULL,'unit','simple',0,NULL,NULL,0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL),(35,1,NULL,NULL,'Camisa talla L color Azul','CAM-1',NULL,NULL,NULL,NULL,'unit','variant',0,34,'{\"Talla\":\"L\",\"Color\":\"Azul\"}',0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL),(36,1,NULL,NULL,'Camisa talla L color Rojo','CAM-2',NULL,NULL,NULL,NULL,'unit','variant',0,34,'{\"Talla\":\"L\",\"Color\":\"Rojo\"}',0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL),(37,1,NULL,NULL,'Camisa talla M color Rojo','CAM-4',NULL,NULL,NULL,NULL,'unit','variant',0,34,'{\"Talla\":\"M\",\"Color\":\"Rojo\"}',0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL),(38,1,NULL,NULL,'Camisa talla S color Azul','CAM-5',NULL,NULL,NULL,NULL,'unit','variant',0,34,'{\"Talla\":\"S\",\"Color\":\"Azul\"}',0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL),(39,1,NULL,NULL,'Camisa talla S color Rojo','CAM-6',NULL,NULL,NULL,NULL,'unit','variant',0,34,'{\"Talla\":\"S\",\"Color\":\"Rojo\"}',0.00,0.00,1,1,'2026-06-29 10:24:49','2026-06-29 10:24:49',NULL);
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `purchase_items_company_id_index` (`company_id`),
  CONSTRAINT `purchase_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,1,2,'Agua 600ml','BEB-0002',1.000,200.00,0.00,0.00,200.00,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(2,1,2,2,'Agua 600ml','BEB-0002',1.000,200.00,0.00,0.00,200.00,'2026-02-16 07:00:07','2026-02-16 07:00:07'),(3,1,3,6,'Barra de Chocolate 90g','SNK-0003',1.000,1000.00,16.00,160.00,1160.00,'2026-02-24 07:54:26','2026-02-24 07:54:26'),(4,1,3,3,'Cafe Molido Premium 250g','BEB-0003',1.000,2000.00,16.00,320.00,2320.00,'2026-02-24 07:54:27','2026-02-24 07:54:27');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `purchase_payments_company_id_index` (`company_id`),
  CONSTRAINT `purchase_payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
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
INSERT INTO `purchase_payments` VALUES (1,1,2,4,'transfer',100.00,'k441','2026-05-04 19:46:21',NULL,NULL,NULL,'2026-02-16 07:01:20','2026-02-16 07:01:20');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `purchases_company_id_index` (`company_id`),
  KEY `purchases_company_id_branch_id_purchased_at_index` (`company_id`,`branch_id`,`purchased_at`),
  CONSTRAINT `purchases_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,1,1,4,1,'posted','aguas','10520262','5525541',200.00,0.00,200.00,200.00,0.00,'cash','2026-05-04 19:46:21',NULL,'2026-02-15 09:19:09','2026-02-15 09:19:09'),(2,1,1,4,2,'posted','Libis','10520262','55255411',200.00,0.00,200.00,100.00,100.00,'credit','2026-05-04 19:46:21',NULL,'2026-02-16 07:00:07','2026-02-16 07:01:20'),(3,1,1,4,3,'posted','proveedor mas','1112200','75225521',3000.00,480.00,3480.00,0.00,3480.00,'credit','2026-05-04 19:46:21',NULL,'2026-02-24 07:54:26','2026-02-24 07:54:26');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_order_item_selections`
--

DROP TABLE IF EXISTS `restaurant_order_item_selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_order_item_selections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `restaurant_order_item_id` bigint(20) unsigned NOT NULL,
  `product_modifier_group_id` bigint(20) unsigned DEFAULT NULL,
  `product_modifier_option_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `group_name` varchar(255) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `selection_action` varchar(20) NOT NULL DEFAULT 'include',
  `price_delta` decimal(12,2) NOT NULL DEFAULT 0.00,
  `inventory_quantity` decimal(12,3) NOT NULL DEFAULT 0.000,
  `inventory_unit` varchar(32) DEFAULT NULL,
  `inventory_unit_factor` decimal(12,6) NOT NULL DEFAULT 1.000000,
  `stock_quantity` decimal(12,6) NOT NULL DEFAULT 0.000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurant_order_item_selections_company_id_foreign` (`company_id`),
  CONSTRAINT `restaurant_order_item_selections_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_order_item_selections`
--

LOCK TABLES `restaurant_order_item_selections` WRITE;
/*!40000 ALTER TABLE `restaurant_order_item_selections` DISABLE KEYS */;
INSERT INTO `restaurant_order_item_selections` VALUES (1,3,8,4,11,NULL,'Proteina','Carne','include',0.00,0.000,NULL,1.000000,0.000000,'2026-05-05 05:40:33','2026-05-05 05:40:33'),(2,3,8,5,15,NULL,'Acompanantes','Arroz','include',0.00,0.000,NULL,1.000000,0.000000,'2026-05-05 05:40:33','2026-05-05 05:40:33'),(3,3,8,5,16,NULL,'Acompanantes','Ensalada','include',0.00,0.000,NULL,1.000000,0.000000,'2026-05-05 05:40:33','2026-05-05 05:40:33'),(4,3,9,10,32,27,'Proteina','Pollo','include',0.00,1.000,'libra',300.000000,300.000000,'2026-05-06 14:14:43','2026-05-06 14:14:43'),(5,3,10,10,32,27,'Proteina','Pollo','include',0.00,1.000,'libra',300.000000,300.000000,'2026-05-06 14:17:11','2026-05-06 14:17:11'),(6,3,11,11,35,NULL,'Proteina','Pechuga','include',1500.00,1.000,NULL,1.000000,1.000000,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(7,3,11,12,37,NULL,'Acompanantes','Arroz','include',0.00,1.000,NULL,1.000000,1.000000,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(8,3,11,12,38,NULL,'Acompanantes','Ensalada','include',0.00,1.000,NULL,1.000000,1.000000,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(9,3,11,13,41,NULL,'Quitar ingredientes','Cebolla','remove',0.00,1.000,NULL,1.000000,1.000000,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(10,3,11,13,42,NULL,'Quitar ingredientes','Ensalada','remove',0.00,1.000,NULL,1.000000,1.000000,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(11,2,12,17,47,32,'Tips','mi compuesto','include',0.00,250.000,'g',0.002000,0.500000,'2026-06-07 05:34:19','2026-06-07 05:34:19');
/*!40000 ALTER TABLE `restaurant_order_item_selections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_order_items`
--

DROP TABLE IF EXISTS `restaurant_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `restaurant_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `kitchen_status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurant_order_items_restaurant_order_id_foreign` (`restaurant_order_id`),
  KEY `restaurant_order_items_product_id_foreign` (`product_id`),
  KEY `restaurant_order_items_company_id_restaurant_order_id_index` (`company_id`,`restaurant_order_id`),
  KEY `restaurant_order_items_company_id_kitchen_status_index` (`company_id`,`kitchen_status`),
  CONSTRAINT `restaurant_order_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `restaurant_order_items_restaurant_order_id_foreign` FOREIGN KEY (`restaurant_order_id`) REFERENCES `restaurant_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_order_items`
--

LOCK TABLES `restaurant_order_items` WRITE;
/*!40000 ALTER TABLE `restaurant_order_items` DISABLE KEYS */;
INSERT INTO `restaurant_order_items` VALUES (1,3,2,1,1.000,1.20,1.20,'Sin observaciones','pending','2026-05-05 04:37:27','2026-05-05 04:37:27'),(4,3,5,3,1.000,4.80,4.80,'Extra salsa','pending','2026-05-05 05:14:16','2026-05-05 05:14:16'),(5,3,5,4,2.000,1.50,3.00,'Compartir','pending','2026-05-05 05:14:16','2026-05-05 05:14:16'),(6,3,6,5,1.000,1.90,1.90,'Sin cebolla','ready','2026-05-05 05:14:16','2026-05-05 05:14:16'),(7,3,7,6,1.000,1.70,1.70,'Empaque aparte','pending','2026-05-05 05:14:16','2026-05-05 05:14:16'),(8,3,3,21,1.000,16000.00,16000.00,NULL,'pending','2026-05-05 05:40:33','2026-05-05 05:40:33'),(10,3,4,28,1.000,20000.00,20000.00,NULL,'pending','2026-05-06 14:17:11','2026-05-06 14:17:11'),(11,3,10,21,1.000,17500.00,17500.00,NULL,'pending','2026-05-22 09:53:09','2026-05-22 09:53:09'),(12,2,11,33,1.000,12000.00,12000.00,NULL,'delivered','2026-06-07 05:34:19','2026-06-07 06:34:09'),(13,2,12,32,1.000,2000.00,2000.00,NULL,'pending','2026-06-07 06:33:04','2026-06-07 06:33:04'),(14,2,13,32,1.000,2000.00,2000.00,NULL,'delivered','2026-06-07 06:54:02','2026-06-07 06:54:40');
/*!40000 ALTER TABLE `restaurant_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_orders`
--

DROP TABLE IF EXISTS `restaurant_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  `order_number` bigint(20) unsigned NOT NULL,
  `order_type` varchar(30) NOT NULL DEFAULT 'dine_in',
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_orders_branch_id_order_number_unique` (`branch_id`,`order_number`),
  KEY `restaurant_orders_restaurant_table_id_foreign` (`restaurant_table_id`),
  KEY `restaurant_orders_user_id_foreign` (`user_id`),
  KEY `restaurant_orders_customer_id_foreign` (`customer_id`),
  KEY `restaurant_orders_sale_id_foreign` (`sale_id`),
  KEY `restaurant_orders_company_id_branch_id_status_index` (`company_id`,`branch_id`,`status`),
  KEY `restaurant_orders_company_id_sale_id_index` (`company_id`,`sale_id`),
  CONSTRAINT `restaurant_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `restaurant_orders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_orders_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_orders_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_orders`
--

LOCK TABLES `restaurant_orders` WRITE;
/*!40000 ALTER TABLE `restaurant_orders` DISABLE KEYS */;
INSERT INTO `restaurant_orders` VALUES (1,1,1,1,9,NULL,NULL,1,'dine_in','open',0.00,0.00,0.00,0.00,NULL,'2026-05-05 04:16:44',NULL,'2026-05-05 04:16:44','2026-05-05 04:16:44'),(2,3,3,2,11,2,NULL,1,'dine_in','sent_to_kitchen',1.20,0.19,0.00,1.39,'Prueba visual restaurante','2026-05-05 04:37:26',NULL,'2026-05-05 04:37:26','2026-05-05 04:37:27'),(3,3,3,3,11,NULL,NULL,2,'dine_in','sent_to_kitchen',16000.00,0.00,0.00,16000.00,NULL,'2026-05-05 05:11:38',NULL,'2026-05-05 05:11:38','2026-05-05 05:40:44'),(4,3,3,4,11,8,NULL,3,'dine_in','open',20000.00,0.00,0.00,20000.00,'Pedido demo mesa abierta','2026-05-05 05:14:16',NULL,'2026-05-05 05:14:16','2026-05-06 14:14:43'),(5,3,3,5,11,2,NULL,4,'dine_in','sent_to_kitchen',7.80,1.25,0.00,9.05,'Pedido demo mesa en cocina','2026-05-05 05:14:16',NULL,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(6,3,3,6,11,2,NULL,5,'dine_in','ready',1.90,0.30,0.00,2.20,'Pedido demo mesa lista','2026-05-05 05:14:16',NULL,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(7,3,3,NULL,11,2,NULL,6,'takeaway','open',1.70,0.27,0.00,1.97,'Pedido demo para llevar','2026-05-05 05:14:16',NULL,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(8,2,2,NULL,10,NULL,NULL,1,'delivery','open',0.00,0.00,0.00,0.00,NULL,'2026-05-06 13:51:01',NULL,'2026-05-06 13:51:01','2026-05-06 13:51:01'),(9,3,3,NULL,11,NULL,NULL,7,'delivery','open',0.00,0.00,0.00,0.00,NULL,'2026-05-06 14:00:45',NULL,'2026-05-06 14:00:45','2026-05-06 14:00:45'),(10,3,3,NULL,11,10,NULL,8,'delivery','sent_to_kitchen',17500.00,0.00,0.00,17500.00,'Origen: Pedido web restaurante\r\nTipo de pedido: Domicilio\r\nMetodo de pago sugerido: Contraentrega\r\nDireccion: Calle Principal 123\r\nTelefono: 3214555115','2026-05-22 09:53:09',NULL,'2026-05-22 09:53:09','2026-05-22 09:53:09'),(11,2,2,7,10,NULL,NULL,2,'dine_in','cancelled',12000.00,0.00,0.00,12000.00,NULL,'2026-06-07 05:06:34','2026-06-07 06:49:53','2026-06-07 05:06:34','2026-06-07 06:49:53'),(12,2,2,8,10,NULL,NULL,3,'dine_in','cancelled',2000.00,0.00,0.00,2000.00,NULL,'2026-06-07 06:32:38','2026-06-07 06:49:37','2026-06-07 06:32:38','2026-06-07 06:49:37'),(13,2,2,8,10,NULL,NULL,4,'dine_in','cancelled',2000.00,0.00,0.00,2000.00,NULL,'2026-06-07 06:51:58','2026-06-07 06:58:51','2026-06-07 06:51:58','2026-06-07 06:58:51');
/*!40000 ALTER TABLE `restaurant_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(50) NOT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 1,
  `status` varchar(30) NOT NULL DEFAULT 'available',
  `location` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_tables_branch_id_number_unique` (`branch_id`,`number`),
  KEY `restaurant_tables_company_id_branch_id_status_index` (`company_id`,`branch_id`,`status`),
  CONSTRAINT `restaurant_tables_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `restaurant_tables_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
INSERT INTO `restaurant_tables` VALUES (1,1,1,'Mesa','01',3,'occupied','Sala',1,'2026-05-05 04:16:27','2026-05-05 04:16:44'),(2,3,3,'Mesa prueba restaurante','R-01',4,'occupied','Salon principal',1,'2026-05-05 04:37:26','2026-05-05 04:37:27'),(3,3,3,'Terraza','2',4,'occupied','Salon',1,'2026-05-05 05:10:38','2026-05-05 05:11:38'),(4,3,3,'Ventana 10','R-10',4,'occupied','Ventana',1,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(5,3,3,'Centro 11','R-11',4,'occupied','Salón central',1,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(6,3,3,'Terraza 12','R-12',4,'occupied','Terraza',1,'2026-05-05 05:14:16','2026-05-05 05:14:16'),(7,2,2,'Terraza','T-01',4,'available',NULL,1,'2026-06-07 05:06:03','2026-06-07 06:49:53'),(8,2,2,'mesa','M-02',4,'available','Principal',1,'2026-06-07 06:31:26','2026-06-07 06:58:51'),(9,2,2,'mesa','M-03',5,'available',NULL,1,'2026-06-07 06:51:01','2026-06-07 06:51:01');
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_items`
--

DROP TABLE IF EXISTS `return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `return_items_company_id_index` (`company_id`),
  CONSTRAINT `return_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
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
  KEY `returns_company_id_index` (`company_id`),
  KEY `returns_company_id_branch_id_created_at_index` (`company_id`,`branch_id`,`created_at`),
  CONSTRAINT `returns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `returns_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
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
INSERT INTO `role_has_permissions` VALUES (1,1),(1,5),(2,1),(2,5),(3,1),(3,2),(3,5),(4,1),(4,2),(4,5),(5,1),(5,2),(5,5),(6,1),(6,2),(6,5),(7,1),(7,2),(7,5),(8,1),(8,2),(8,3),(8,5),(9,1),(9,2),(9,3),(9,5),(10,1),(10,2),(10,3),(10,5),(11,1),(11,2),(11,3),(11,5),(12,1),(12,2),(12,3),(12,5),(13,1),(13,2),(13,5),(14,1),(14,2),(14,5),(15,1),(15,2),(15,5),(16,1),(16,2),(16,5),(17,1),(17,2),(17,5),(18,1),(18,2),(18,5),(19,1),(19,2),(19,5),(20,1),(20,5),(21,1),(21,5),(22,1),(22,2),(22,3),(22,5),(23,1),(23,2),(23,3),(23,5),(24,1),(24,2),(24,5),(25,1),(25,2),(25,5),(26,1),(26,2),(26,3),(26,5);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(2,'supervisor','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(3,'cashier','web','2026-02-12 10:18:32','2026-02-12 10:18:32'),(4,'customer','web','2026-02-14 21:48:05','2026-02-14 21:48:05'),(5,'system_owner','web','2026-05-05 00:36:13','2026-05-05 00:36:13');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_item_lots`
--

DROP TABLE IF EXISTS `sale_item_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_item_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `sale_item_id` bigint(20) unsigned NOT NULL,
  `inventory_lot_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `returned_quantity` decimal(12,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_item_lots_company_id_foreign` (`company_id`),
  KEY `sale_item_lots_sale_item_id_foreign` (`sale_item_id`),
  KEY `sale_item_lots_inventory_lot_id_foreign` (`inventory_lot_id`),
  CONSTRAINT `sale_item_lots_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_item_lots_inventory_lot_id_foreign` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots` (`id`),
  CONSTRAINT `sale_item_lots_sale_item_id_foreign` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_item_lots`
--

LOCK TABLES `sale_item_lots` WRITE;
/*!40000 ALTER TABLE `sale_item_lots` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_item_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(64) DEFAULT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
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
  KEY `sale_items_company_id_index` (`company_id`),
  CONSTRAINT `sale_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (2,1,2,9,'Camiseta deportiva talla M','VAR-TSHIRT-M','7501000001011',NULL,1.000,17.90,NULL,0.00,16.00,2.86,20.76,'2026-02-15 08:23:45','2026-02-15 08:23:45'),(3,1,2,15,'Camiseta deportiva talla XL','VAR-TSHIRT-XL','7501000001014',NULL,1.000,18.60,NULL,0.00,16.00,2.98,21.58,'2026-02-15 08:23:46','2026-02-15 08:23:46'),(4,1,3,2,'Agua 600ml','BEB-0002','7501000000028',NULL,1.000,1000.00,NULL,0.00,0.00,0.00,1000.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(5,1,3,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(6,1,4,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:44','2026-02-15 23:44:44'),(7,1,4,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:45','2026-02-15 23:44:45'),(8,1,5,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(9,1,5,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(10,1,6,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(11,1,6,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',NULL,1.000,100.00,NULL,0.00,16.00,16.00,116.00,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(12,1,7,2,'Agua 600ml','BEB-0002','7501000000028',NULL,1.000,1000.00,NULL,0.00,0.00,0.00,1000.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(13,1,7,8,'Cafe base origen','BAS-COFFEE','7501000001002',NULL,1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(14,1,8,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-16 06:55:26','2026-02-16 06:55:26'),(15,1,8,8,'Cafe base origen','BAS-COFFEE','7501000001002',NULL,1.000,1000.00,NULL,0.00,16.00,160.00,1160.00,'2026-02-16 06:55:27','2026-02-16 06:55:27'),(16,1,9,2,'Agua 600ml','BEB-0002','7501000000028',NULL,1.000,0.90,NULL,0.00,0.00,0.00,0.90,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(17,1,9,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,1.70,NULL,0.00,16.00,0.27,1.97,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(18,1,9,10,'Cafe origen en grano 500g','VAR-COFFEE-GRANO','7501000001012',NULL,1.000,8.20,NULL,0.00,16.00,1.31,9.51,'2026-02-16 09:41:56','2026-02-16 09:41:56'),(19,1,10,2,'Agua 600ml','BEB-0002','7501000000028',NULL,1.000,0.90,NULL,0.00,0.00,0.00,0.90,'2026-02-16 09:54:03','2026-02-16 09:54:03'),(20,1,10,6,'Barra de Chocolate 90g','SNK-0003','7501000000066',NULL,1.000,1.70,NULL,0.00,16.00,0.27,1.97,'2026-02-16 09:54:03','2026-02-16 09:54:03'),(21,1,11,1,'Refresco Cola 600ml','BEB-0001','7501000000011',NULL,1.000,50.00,NULL,0.00,16.00,8.00,58.00,'2026-02-19 07:37:05','2026-02-19 07:37:05'),(22,1,12,1,'Refresco Cola 600ml','BEB-0001','7501000000011',NULL,1.000,40.00,NULL,0.00,16.00,6.40,46.40,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(23,1,13,18,'Carne de res','CARRES',NULL,NULL,2.000,15000.00,NULL,0.00,0.00,0.00,30000.00,'2026-02-24 07:47:16','2026-02-24 07:47:16'),(26,1,16,2,'Agua 600ml','BEB-0002','7702184580479',NULL,1.000,2000.00,NULL,0.00,0.00,0.00,2000.00,'2026-06-07 07:07:32','2026-06-07 07:07:32');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `medical_order_id` bigint(20) unsigned DEFAULT NULL,
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
  UNIQUE KEY `sales_medical_order_id_unique` (`medical_order_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_cash_register_session_id_foreign` (`cash_register_session_id`),
  KEY `sales_branch_id_sold_at_index` (`branch_id`,`sold_at`),
  KEY `sales_order_source_index` (`order_source`),
  KEY `sales_invoiced_by_user_id_foreign` (`invoiced_by_user_id`),
  KEY `sales_invoiced_at_index` (`invoiced_at`),
  KEY `sales_accounted_by_user_id_foreign` (`accounted_by_user_id`),
  KEY `sales_accounted_at_index` (`accounted_at`),
  KEY `sales_company_id_index` (`company_id`),
  KEY `sales_company_id_branch_id_sold_at_index` (`company_id`,`branch_id`,`sold_at`),
  CONSTRAINT `sales_accounted_by_user_id_foreign` FOREIGN KEY (`accounted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_cash_register_session_id_foreign` FOREIGN KEY (`cash_register_session_id`) REFERENCES `cash_register_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_invoiced_by_user_id_foreign` FOREIGN KEY (`invoiced_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_medical_order_id_foreign` FOREIGN KEY (`medical_order_id`) REFERENCES `medical_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (2,1,1,7,4,NULL,NULL,1,'pending','ecommerce',36.50,0.00,5.84,8.50,0.00,NULL,'Calle Principal 123',NULL,50.84,50.84,0.00,'USD','2026-05-04 19:46:21','2026-02-16 09:49:06',4,'2026-02-16 11:02:44',4,'2026-02-15 08:23:45','2026-02-16 11:02:44'),(3,1,1,4,NULL,NULL,2,2,'paid','pos',2000.00,0.00,160.00,0.00,0.00,NULL,NULL,NULL,2160.00,2160.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-15 08:48:15','2026-02-15 08:48:15'),(4,1,1,4,1,NULL,2,3,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-15 23:44:44','2026-02-15 23:44:44'),(5,1,1,4,1,NULL,2,4,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-15 23:44:47','2026-02-15 23:44:47'),(6,1,1,4,NULL,NULL,2,5,'paid','pos',200.00,0.00,32.00,0.00,0.00,NULL,NULL,NULL,232.00,232.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-15 23:45:22','2026-02-15 23:45:22'),(7,1,1,4,NULL,NULL,2,6,'paid','pos',2000.00,0.00,160.00,0.00,0.00,NULL,NULL,NULL,2160.00,2160.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-15 23:50:23','2026-02-15 23:50:23'),(8,1,1,4,1,NULL,2,7,'pending','pos',2000.00,0.00,320.00,0.00,0.00,NULL,NULL,NULL,2320.00,0.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-16 06:55:26','2026-02-16 06:55:26'),(9,1,1,7,4,NULL,NULL,8,'pending','ecommerce',10.80,0.00,1.58,8.50,0.00,NULL,'Calle Principal 123','Referencia de pago: FlDKK5557141',20.88,20.88,0.00,'USD','2026-05-04 19:46:21','2026-02-16 09:43:14',4,'2026-02-16 09:55:08',4,'2026-02-16 09:41:56','2026-02-16 09:55:08'),(10,1,1,7,4,NULL,NULL,9,'pending','ecommerce',2.60,0.00,0.27,8.50,0.00,NULL,'Calle Principal 123','Referencia de pago: FlDKK55571411',11.37,11.37,0.00,'USD','2026-05-04 19:46:21','2026-02-16 11:02:33',4,'2026-02-16 11:02:33',4,'2026-02-16 09:54:03','2026-02-16 11:02:33'),(11,1,1,4,2,NULL,2,10,'pending','pos',50.00,0.00,8.00,0.00,0.00,NULL,NULL,NULL,58.00,7.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-19 07:37:05','2026-02-19 07:37:06'),(12,1,1,4,2,NULL,2,11,'pending','pos',40.00,0.00,6.40,0.00,0.00,NULL,NULL,NULL,46.40,7.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-19 07:38:44','2026-02-19 07:38:44'),(13,1,1,4,6,NULL,4,12,'pending','pos',30000.00,0.00,0.00,0.00,0.00,NULL,NULL,NULL,30000.00,20000.00,0.00,'USD','2026-05-04 19:46:21',NULL,NULL,NULL,NULL,'2026-02-24 07:47:16','2026-02-24 07:50:07'),(16,1,1,4,4,NULL,4,13,'paid','pos',2000.00,0.00,0.00,0.00,0.00,NULL,NULL,NULL,2000.00,2000.00,0.00,'USD','2026-06-07 07:07:32',NULL,NULL,NULL,NULL,'2026-06-07 07:07:32','2026-06-07 07:07:32');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_company_id_key_unique` (`company_id`,`key`),
  KEY `settings_company_id_index` (`company_id`),
  CONSTRAINT `settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,1,'business','{\"name\":\"Mi Tienda POS\",\"nit\":\"NIT-000000\",\"address\":\"Calle Principal 123\",\"phone\":\"555-0101\",\"currency\":\"USD\",\"default_tax_id\":\"1\",\"logo_url\":\"https:\\/\\/pub-f655c8961ea043dba809216aad023e1b.r2.dev\\/Pos\\/2026\\/05\\/07ce0787-c29e-4589-a434-f31159af0ec9.jpg\",\"payment_qr_url\":\"https:\\/\\/pub-f655c8961ea043dba809216aad023e1b.r2.dev\\/Pos\\/2026\\/05\\/084803ac-2631-4ad4-83ca-9e988604ae91.jpg\",\"allow_negative_stock\":false}','2026-02-13 00:29:38','2026-05-02 09:14:06'),(2,2,'business','[]','2026-05-05 02:26:31','2026-05-05 02:26:31');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_license_payments`
--

DROP TABLE IF EXISTS `software_license_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_license_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `software_license_id` bigint(20) unsigned NOT NULL,
  `registered_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `validated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(30) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `paid_at` datetime DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_period_unique` (`software_license_id`,`period_start`,`period_end`),
  KEY `software_license_payments_registered_by_user_id_foreign` (`registered_by_user_id`),
  KEY `software_license_payments_validated_by_user_id_foreign` (`validated_by_user_id`),
  KEY `software_license_payments_software_license_id_status_index` (`software_license_id`,`status`),
  CONSTRAINT `software_license_payments_registered_by_user_id_foreign` FOREIGN KEY (`registered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `software_license_payments_software_license_id_foreign` FOREIGN KEY (`software_license_id`) REFERENCES `software_licenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `software_license_payments_validated_by_user_id_foreign` FOREIGN KEY (`validated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_license_payments`
--

LOCK TABLES `software_license_payments` WRITE;
/*!40000 ALTER TABLE `software_license_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_license_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_licenses`
--

DROP TABLE IF EXISTS `software_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_licenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `monthly_amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `starts_at` date NOT NULL,
  `billing_day` tinyint(3) unsigned DEFAULT NULL,
  `paid_through` date DEFAULT NULL,
  `last_payment_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `software_licenses_customer_id_unique` (`customer_id`),
  KEY `software_licenses_status_paid_through_index` (`status`,`paid_through`),
  CONSTRAINT `software_licenses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_licenses`
--

LOCK TABLES `software_licenses` WRITE;
/*!40000 ALTER TABLE `software_licenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxes`
--

DROP TABLE IF EXISTS `taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxes_company_id_index` (`company_id`),
  CONSTRAINT `taxes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxes`
--

LOCK TABLES `taxes` WRITE;
/*!40000 ALTER TABLE `taxes` DISABLE KEYS */;
INSERT INTO `taxes` VALUES (1,1,'IVA 16%',16.00,1,'2026-02-14 21:48:06','2026-02-14 21:48:06'),(2,1,'Exento 0%',0.00,1,'2026-02-14 21:48:06','2026-02-14 21:48:06');
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
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `is_platform_owner` tinyint(1) NOT NULL DEFAULT 0,
  `is_customer_owner` tinyint(1) NOT NULL DEFAULT 0,
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
  KEY `users_customer_id_is_platform_owner_index` (`customer_id`,`is_platform_owner`),
  KEY `users_customer_id_is_customer_owner_index` (`customer_id`,`is_customer_owner`),
  KEY `users_company_id_index` (`company_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,1,1,NULL,0,0,'Administrador','ldtapiaposada@gmail.com',NULL,'$2y$12$2BeuFf9QHFrLycaxej743u2lH.uZvDhbg/0vi1lfULus2GC4m/BdS','jhGZnu0EnDxSu9QeWG33gK7Jh37NoQs0RioiKnptsqHI90XvdDnCMh9E2hcC','2026-02-12 10:08:21','2026-02-17 07:54:04'),(5,1,1,NULL,0,0,'[DEMO DISABLED] Supervisor','disabled+supervisor-at-pos-test@invalid.local',NULL,'$2y$12$VrdsrX9mREc6YifrLuUd4eZ/7SinXmo/q6JtQZSpPX5y2UNHJXiqa',NULL,'2026-02-12 10:08:21','2026-06-01 02:04:11'),(6,1,1,NULL,0,0,'[DEMO DISABLED] Cajero','disabled+cashier-at-pos-test@invalid.local',NULL,'$2y$12$pGubvQ98O49kM0/WXloGXOgvHM/GUwcIBaDC..1GeFdKX11ZOOpxC',NULL,'2026-02-12 10:08:22','2026-06-01 02:04:12'),(7,NULL,NULL,NULL,0,0,'Libis marquez','libis@gmail.com',NULL,'$2y$12$t70GLJYYPy8u7xL8vX9iTeqlpqN7fl5cHt3DYvob40Rdlmc9HXnUO',NULL,'2026-02-15 08:02:15','2026-02-15 08:02:15'),(8,NULL,NULL,NULL,0,0,'lucho','lucho@gmail.com',NULL,'$2y$12$ESUHf9En.cOA8ouUzBDp6OtRNtBHthMg/G/qKyWu8XqlP/IdNKU6W',NULL,'2026-05-02 09:16:02','2026-05-02 09:16:02'),(9,1,1,NULL,0,0,'[DEMO DISABLED] Administrador','disabled+admin-at-pos-test@invalid.local',NULL,'$2y$12$zkywXfjYUHrlYr.cAZW0d.w8fNYd6547IlntEIEC50ESuzof.ln5y',NULL,'2026-05-05 00:36:14','2026-06-01 02:04:12'),(10,2,2,NULL,0,0,'Restaurante','ltapiap@gmail.com',NULL,'$2y$12$xaI3jZHNG.13ov0hbaEb3.2s0upfojnir7xR9P64v7svhwhzMTReO',NULL,'2026-05-05 02:24:49','2026-05-05 02:24:49'),(11,3,3,NULL,0,0,'Restaurant Admin','restaurant@pos.test',NULL,'$2y$12$xaI3jZHNG.13ov0hbaEb3.2s0upfojnir7xR9P64v7svhwhzMTReO',NULL,'2026-05-05 04:36:06','2026-05-05 04:36:06'),(12,2,2,NULL,0,0,'Validador de contexto','context@pos.test',NULL,'$2y$12$8eEpxza5yQyftthEyNWWouBYCuV0P6vsQT22ohw/q632nx8mO6QDC',NULL,'2026-05-06 13:43:21','2026-05-06 13:43:21'),(13,1,1,NULL,0,0,'Luis torres','carnes@gmail.com',NULL,'$2y$12$5.BYZTZrHwbMe0T.srccZO5mUGgTT3gFoZaOvvP4lmXPKV631Y6Rm',NULL,'2026-05-22 09:34:47','2026-05-22 09:34:47'),(14,3,NULL,NULL,0,0,'luis torres','carne2@gmail.com',NULL,'$2y$12$aA.616/zNYiqf9ZRD2Zn0u.MC.qFatK0Uz4t/Q78eep1i8gNIpXBG',NULL,'2026-05-22 09:43:26','2026-05-22 09:43:26'),(15,4,4,NULL,0,0,'optica','mioptica@gmail.com',NULL,'$2y$12$HzzoUsUUSS0yIyuN77xHFuhp1oBSNGlWCSkpohzBNMAh2FN8rHqEe',NULL,'2026-06-01 05:12:46','2026-06-01 05:12:46'),(16,1,1,NULL,0,0,'Libis marquez','luis@gmail.com',NULL,'$2y$12$juHHoubo9kb6uLHrAEImhu4Zckmb7PEvK3O61eVFwyoDhFBZ7vKhy',NULL,'2026-09-03 09:53:17','2026-09-03 09:53:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

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

-- Dump completed on 2026-09-03 10:29:41
