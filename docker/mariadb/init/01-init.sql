-- ============================================
-- APIDIAN - Inicialización de Base de Datos
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Otorgar privilegios completos al usuario de la aplicación
GRANT ALL PRIVILEGES ON *.* TO 'apidian'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
