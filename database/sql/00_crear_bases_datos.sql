-- Instalación nueva: crea las únicas dos bases requeridas por la plataforma.
-- Ejecutar con un usuario MySQL/MariaDB que tenga permiso CREATE DATABASE.
-- Es seguro volver a ejecutarlo: no elimina ni modifica datos existentes.
--
-- POS y e-commerce comparten pos_core. Administración usa administracion.

CREATE DATABASE IF NOT EXISTS pos_core
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS administracion
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
