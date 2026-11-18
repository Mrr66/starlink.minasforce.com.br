-- Campos de imagem e localização dos produtos
-- Compatível com MySQL e MariaDB e seguro para bancos onde uma das colunas já exista.

SET @schema_name = DATABASE();

SET @sql_image = IF(
    EXISTS(
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'sys_items'
          AND COLUMN_NAME = 'image'
    ),
    'SELECT ''A coluna image já existe'' AS resultado',
    'ALTER TABLE `sys_items` ADD COLUMN `image` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `description`'
);
PREPARE stmt_image FROM @sql_image;
EXECUTE stmt_image;
DEALLOCATE PREPARE stmt_image;

SET @sql_location = IF(
    EXISTS(
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @schema_name
          AND TABLE_NAME = 'sys_items'
          AND COLUMN_NAME = 'location'
    ),
    'SELECT ''A coluna location já existe'' AS resultado',
    'ALTER TABLE `sys_items` ADD COLUMN `location` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `image`'
);
PREPARE stmt_location FROM @sql_location;
EXECUTE stmt_location;
DEALLOCATE PREPARE stmt_location;

SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'sys_items'
  AND COLUMN_NAME IN ('image', 'location')
ORDER BY ORDINAL_POSITION;

