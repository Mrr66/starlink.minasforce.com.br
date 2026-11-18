-- ============================================================
-- Módulo de Apuração Mensal
-- Compatível com MySQL / MariaDB
-- Pode ser executado mais de uma vez com segurança.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `sys_apuracoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `competencia` DATE NOT NULL,
  `status` ENUM('Aberto', 'Fechado') NOT NULL DEFAULT 'Aberto',
  `total_dinheiro` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `total_pix` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `total_boleto` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `total_outras_receitas` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `total_receita` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `total_despesa` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `lucro_liquido` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `created_by` INT(11) NOT NULL DEFAULT '0',
  `finalized_by` INT(11) NOT NULL DEFAULT '0',
  `created_at` DATETIME DEFAULT NULL,
  `finalized_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sys_apuracoes_competencia` (`competencia`),
  KEY `idx_sys_apuracoes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_apuracao_receitas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `apuracao_id` INT(11) NOT NULL,
  `transaction_id` INT(11) NOT NULL,
  `approved` TINYINT(1) NOT NULL DEFAULT '0',
  `approved_by` INT(11) NOT NULL DEFAULT '0',
  `approved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apuracao_receita_transacao` (`apuracao_id`, `transaction_id`),
  KEY `idx_apuracao_receitas_transacao` (`transaction_id`),
  KEY `idx_apuracao_receitas_aprovada` (`apuracao_id`, `approved`),
  CONSTRAINT `fk_apuracao_receitas_apuracao`
    FOREIGN KEY (`apuracao_id`) REFERENCES `sys_apuracoes` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_apuracao_despesas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `apuracao_id` INT(11) NOT NULL,
  `transaction_id` INT(11) NOT NULL,
  `payer_user_id` INT(11) NOT NULL DEFAULT '-1'
    COMMENT '-1 = não definido; 0 = compartilhado; maior que 0 = sys_users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apuracao_despesa_transacao` (`apuracao_id`, `transaction_id`),
  KEY `idx_apuracao_despesas_transacao` (`transaction_id`),
  KEY `idx_apuracao_despesas_pagador` (`payer_user_id`),
  CONSTRAINT `fk_apuracao_despesas_apuracao`
    FOREIGN KEY (`apuracao_id`) REFERENCES `sys_apuracoes` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_apuracao_repasses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `apuracao_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `user_name` VARCHAR(200) NOT NULL,
  `receitas_recebidas` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `despesas_pagas` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `lucro` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  `valor_repasse` DECIMAL(18,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apuracao_repasse_usuario` (`apuracao_id`, `user_id`),
  KEY `idx_apuracao_repasses_usuario` (`user_id`),
  CONSTRAINT `fk_apuracao_repasses_apuracao`
    FOREIGN KEY (`apuracao_id`) REFERENCES `sys_apuracoes` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conferência: ao final devem ser retornadas quatro tabelas.
SELECT `TABLE_NAME`
FROM `information_schema`.`TABLES`
WHERE `TABLE_SCHEMA` = DATABASE()
  AND `TABLE_NAME` IN (
    'sys_apuracoes',
    'sys_apuracao_receitas',
    'sys_apuracao_despesas',
    'sys_apuracao_repasses'
  )
ORDER BY `TABLE_NAME`;
