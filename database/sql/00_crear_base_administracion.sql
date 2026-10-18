-- Crear la base administrativa vacía.
-- Ejecutar con un usuario MySQL que tenga permiso CREATE DATABASE.
-- Es seguro ejecutarlo más de una vez: no elimina una base existente.

CREATE DATABASE IF NOT EXISTS `administracion`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
