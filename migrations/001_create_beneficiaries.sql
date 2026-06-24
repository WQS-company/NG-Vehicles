-- Migration: create beneficiaries and earnings tables
-- Run this SQL against your database to add beneficiary support

CREATE TABLE IF NOT EXISTS `beneficiaries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `role_title` VARCHAR(100) DEFAULT 'Officer',
  `commission_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `is_suspended` TINYINT(1) DEFAULT 0,
  `bank_name` VARCHAR(150) DEFAULT NULL,
  `account_name` VARCHAR(150) DEFAULT NULL,
  `account_number` VARCHAR(50) DEFAULT NULL,
  `meta` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`)
);

CREATE TABLE IF NOT EXISTS `beneficiary_earnings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `beneficiary_id` INT NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reference` VARCHAR(100) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(`beneficiary_id`)
);
