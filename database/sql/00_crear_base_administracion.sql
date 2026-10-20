-- Compatibilidad: para una instalación nueva usa 00_crear_bases_datos.sql,
-- que crea tanto pos_core como administracion.
-- Este archivo solo crea la base administrativa.

CREATE DATABASE IF NOT EXISTS `administracion`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
