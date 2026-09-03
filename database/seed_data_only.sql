
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `accounting_accounts` WRITE;
/*!40000 ALTER TABLE `accounting_accounts` DISABLE KEYS */;
INSERT INTO `accounting_accounts` (`id`, `company_id`, `code`, `name`, `type`, `nature`, `parent_account_id`, `level`, `is_postable`, `is_active`, `created_at`, `updated_at`) VALUES (1,1,'1','Activo','asset','debit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(2,1,'11','Disponible','asset','debit',1,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(3,1,'1105','Caja','asset','debit',2,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(4,1,'1110','Bancos','asset','debit',2,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(5,1,'13','Deudores','asset','debit',1,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(6,1,'1305','Clientes','asset','debit',5,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(7,1,'14','Inventarios','asset','debit',1,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(8,1,'1435','Mercancias no fabricadas','asset','debit',7,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(9,1,'2','Pasivo','liability','credit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(10,1,'22','Proveedores','liability','credit',9,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(11,1,'2205','Nacionales','liability','credit',10,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(12,1,'24','Impuestos por pagar','liability','credit',9,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(13,1,'2408','IVA por pagar','liability','credit',12,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(14,1,'3','Patrimonio','equity','credit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(15,1,'31','Capital social','equity','credit',14,2,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(16,1,'36','Resultados del ejercicio','equity','credit',14,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(17,1,'3605','Utilidad o perdida del ejercicio','equity','credit',16,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(18,1,'4','Ingresos','income','credit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(19,1,'41','Ingresos operacionales','income','credit',18,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(20,1,'4135','Comercio al por menor','income','credit',19,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(21,1,'5','Gastos','expense','debit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(22,1,'51','Administracion','expense','debit',21,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(23,1,'5105','Gastos de personal','expense','debit',22,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(24,1,'5135','Servicios','expense','debit',22,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(25,1,'6','Costo de ventas','expense','debit',NULL,1,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(26,1,'61','Costo de mercancias vendidas','expense','debit',25,2,0,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(27,1,'6135','Comercio al por menor','expense','debit',26,4,1,1,'2026-06-26 14:31:52','2026-06-26 14:31:52');
/*!40000 ALTER TABLE `accounting_accounts` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` (`id`, `company_id`, `name`, `code`, `address`, `phone`, `created_at`, `updated_at`) VALUES (1,1,'Sucursal Principal','PRN','Direccion principal','000-0000','2026-06-26 14:31:51','2026-06-26 14:31:51');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `company_id`, `name`, `description`, `parent_id`, `created_at`, `updated_at`) VALUES (1,1,'Bebidas','Bebidas y refrescos',NULL,'2026-06-26 14:31:51','2026-06-26 14:31:51'),(2,1,'Snacks','Snacks y botanas',NULL,'2026-06-26 14:31:51','2026-06-26 14:31:51');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `company_subscriptions` WRITE;
/*!40000 ALTER TABLE `company_subscriptions` DISABLE KEYS */;
INSERT INTO `company_subscriptions` (`id`, `company_id`, `plan_type`, `billing_period`, `start_date`, `end_date`, `status`, `payment_status`, `last_payment_date`, `next_payment_date`, `created_at`, `updated_at`) VALUES (1,1,'pos','yearly','2026-06-26','2027-06-26','active','paid','2026-06-26','2027-06-26','2026-06-26 14:31:41','2026-06-26 14:31:41');
/*!40000 ALTER TABLE `company_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `company_types` WRITE;
/*!40000 ALTER TABLE `company_types` DISABLE KEYS */;
INSERT INTO `company_types` (`id`, `name`, `slug`, `features`, `is_active`, `created_at`, `updated_at`) VALUES (1,'Restaurante','restaurant','[\"tables\",\"orders\",\"kitchen\",\"menu\"]',1,'2026-06-26 14:31:41','2026-06-26 14:31:41'),(2,'Óptica','optic','[\"optical_prescriptions\",\"lenses\",\"frames\",\"patients\"]',1,'2026-06-26 14:31:41','2026-06-26 14:31:41'),(3,'POS normal','pos','[\"sales\",\"products\",\"inventory\"]',1,'2026-06-26 14:31:41','2026-06-26 14:31:41');
/*!40000 ALTER TABLE `company_types` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` (`id`, `company_id`, `user_id`, `name`, `document`, `email`, `phone`, `address`, `contact_type`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,1,NULL,'Cliente Mostrador','CF',NULL,NULL,NULL,'person',1,'2026-06-26 14:31:51','2026-06-26 14:31:51',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (1,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'manage_optometry_patients','web','2026-06-26 14:31:49','2026-06-26 14:31:49'),(2,'manage_optometry_records','web','2026-06-26 14:31:49','2026-06-26 14:31:49'),(3,'manage_optometry_orders','web','2026-06-26 14:31:49','2026-06-26 14:31:49'),(4,'manage_users','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(5,'manage_settings','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(6,'manage_companies','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(7,'manage_subscriptions','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(8,'manage_products','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(9,'manage_categories','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(10,'manage_branches','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(11,'manage_customers','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(12,'manage_inventory','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(13,'view_reports','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(14,'open_cash_register','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(15,'close_cash_register','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(16,'record_cash_movement','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(17,'create_sale','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(18,'apply_discount','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(19,'apply_high_discount','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(20,'void_sale','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(21,'process_return','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(22,'manage_purchases','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(23,'manage_accounting','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(24,'manage_ecommerce_orders','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(25,'manage_restaurant','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(26,'manage_restaurant_kitchen','web','2026-06-26 14:31:51','2026-06-26 14:31:51');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'admin','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(2,'supervisor','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(3,'cashier','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(4,'customer','web','2026-06-26 14:31:51','2026-06-26 14:31:51'),(5,'system_owner','web','2026-06-26 14:31:51','2026-06-26 14:31:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (1,1),(1,2),(1,5),(2,1),(2,2),(2,5),(3,1),(3,2),(3,3),(3,5),(4,1),(4,5),(5,1),(5,5),(6,1),(6,5),(7,1),(7,5),(8,1),(8,2),(8,5),(9,1),(9,2),(9,5),(10,1),(10,2),(10,5),(11,1),(11,2),(11,5),(12,1),(12,2),(12,5),(13,1),(13,2),(13,5),(14,1),(14,2),(14,3),(14,5),(15,1),(15,2),(15,3),(15,5),(16,1),(16,2),(16,3),(16,5),(17,1),(17,2),(17,3),(17,5),(18,1),(18,2),(18,3),(18,5),(19,1),(19,2),(19,5),(20,1),(20,2),(20,5),(21,1),(21,2),(21,5),(22,1),(22,2),(22,5),(23,1),(23,2),(23,5),(24,1),(24,2),(24,5),(25,1),(25,2),(25,3),(25,5),(26,1),(26,2),(26,3),(26,5);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `company_id`, `key`, `value`, `created_at`, `updated_at`) VALUES (1,1,'business','{\"name\":\"Mi Tienda POS\",\"nit\":\"NIT-000000\",\"address\":\"Calle Principal 123\",\"phone\":\"555-0101\",\"currency\":\"USD\",\"ecommerce_flat_shipping\":8.5,\"ecommerce_coupons\":{\"BIENVENIDO10\":10,\"PROMO15\":15},\"allow_negative_stock\":false,\"default_tax_id\":1}','2026-06-26 14:31:52','2026-06-26 14:31:52');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `taxes` WRITE;
/*!40000 ALTER TABLE `taxes` DISABLE KEYS */;
INSERT INTO `taxes` (`id`, `company_id`, `name`, `rate`, `is_active`, `created_at`, `updated_at`) VALUES (1,1,'IVA 16%',16.00,1,'2026-06-26 14:31:52','2026-06-26 14:31:52'),(2,1,'Exento 0%',0.00,1,'2026-06-26 14:31:52','2026-06-26 14:31:52');
/*!40000 ALTER TABLE `taxes` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `company_id`, `branch_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1,1,1,'Administrador','ldtapiaposada@gmail.com',NULL,'$2y$12$KpHmx7ymRSbFkxWp2y3qXOP.ACPeOsNGV//azWVtZBjg.IegnTsRm',NULL,'2026-06-26 14:31:52','2026-06-26 14:31:52');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

