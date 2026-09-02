-- Code X Database Schema
-- System: Code X - AI-Powered Personal Financial Management and Intelligent Financial Guidance System
-- MySQL Version: 5.7+ / 8.0+

CREATE DATABASE IF NOT EXISTS `code_x_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `code_x_db`;

-- Drop existing tables if re-initializing
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `system_activity_logs`;
DROP TABLE IF EXISTS `ai_messages`;
DROP TABLE IF EXISTS `ai_conversations`;
DROP TABLE IF EXISTS `financial_goals`;
DROP TABLE IF EXISTS `budgets`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('income', 'expense') NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'fa-tag',
  `color` VARCHAR(20) DEFAULT '#4e73df',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Transactions Table
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `transaction_type` ENUM('income', 'expense') NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `description` TEXT NULL,
  `transaction_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Budgets Table
CREATE TABLE `budgets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `month` INT NOT NULL,
  `year` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_budgets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budgets_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `user_category_month_year` (`user_id`, `category_id`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Financial Goals Table
CREATE TABLE `financial_goals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `target_amount` DECIMAL(12, 2) NOT NULL,
  `current_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `target_date` DATE NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_goals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. AI Conversations Table
CREATE TABLE `ai_conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL DEFAULT 'Financial Guidance Session',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ai_conv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. AI Messages Table
CREATE TABLE `ai_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` ENUM('user', 'assistant') NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ai_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ai_msg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. System Activity Logs Table
CREATE TABLE `system_activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL DEFAULT NULL,
  `activity` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Categories (Global, user_id = NULL)
INSERT INTO `categories` (`user_id`, `name`, `type`, `icon`, `color`) VALUES
(NULL, 'Salary', 'income', 'fa-wallet', '#10b981'),
(NULL, 'Business', 'income', 'fa-briefcase', '#06b6d4'),
(NULL, 'Freelance', 'income', 'fa-laptop-code', '#3b82f6'),
(NULL, 'Gift', 'income', 'fa-gift', '#8b5cf6'),
(NULL, 'Other Income', 'income', 'fa-coins', '#64748b'),
(NULL, 'Food & Groceries', 'expense', 'fa-utensils', '#ef4444'),
(NULL, 'Transport', 'expense', 'fa-bus', '#f97316'),
(NULL, 'Rent & Housing', 'expense', 'fa-home', '#f59e0b'),
(NULL, 'Education', 'expense', 'fa-graduation-cap', '#6366f1'),
(NULL, 'Healthcare', 'expense', 'fa-heartbeat', '#ec4899'),
(NULL, 'Entertainment', 'expense', 'fa-film', '#a855f7'),
(NULL, 'Utilities', 'expense', 'fa-bolt', '#14b8a6'),
(NULL, 'Other Expense', 'expense', 'fa-shopping-bag', '#94a3b8');

-- Seed System Users
-- Password for all seed users is: password123 (hashed using PASSWORD_BCRYPT)
-- Hash: $2y$10$F11WelePm4/aQExbK5NeDejAEbHrBDCMB.kV2DWK19XeJYVq07eQS
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`) VALUES
(1, 'Code X System Admin', 'admin@codex.com', '$2y$10$F11WelePm4/aQExbK5NeDejAEbHrBDCMB.kV2DWK19XeJYVq07eQS', 'admin'),
(2, 'Alex Johnson (Demo User)', 'user@codex.com', '$2y$10$F11WelePm4/aQExbK5NeDejAEbHrBDCMB.kV2DWK19XeJYVq07eQS', 'user');

-- Seed Sample Data for Demo User (user_id = 2)
INSERT INTO `transactions` (`user_id`, `category_id`, `transaction_type`, `amount`, `description`, `transaction_date`) VALUES
(2, 1, 'income', 450000.00, 'Monthly Main Salary', CURRENT_DATE - INTERVAL 25 DAY),
(2, 3, 'income', 85000.00, 'Freelance Web Design Project', CURRENT_DATE - INTERVAL 12 DAY),
(2, 6, 'expense', 65000.00, 'Supermarket Groceries', CURRENT_DATE - INTERVAL 20 DAY),
(2, 7, 'expense', 25000.00, 'Fuel and Public Transit Pass', CURRENT_DATE - INTERVAL 18 DAY),
(2, 8, 'expense', 120000.00, 'Monthly Apartment Rent', CURRENT_DATE - INTERVAL 24 DAY),
(2, 9, 'expense', 35000.00, 'IT Certification Course', CURRENT_DATE - INTERVAL 15 DAY),
(2, 11, 'expense', 18000.00, 'Movies & Weekend Outing', CURRENT_DATE - INTERVAL 8 DAY),
(2, 12, 'expense', 22000.00, 'Electricity & Internet Bill', CURRENT_DATE - INTERVAL 5 DAY);

-- Seed Sample Budgets for Demo User
INSERT INTO `budgets` (`user_id`, `category_id`, `amount`, `month`, `year`) VALUES
(2, 6, 80000.00, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE)),
(2, 7, 30000.00, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE)),
(2, 8, 120000.00, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE)),
(2, 11, 15000.00, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE));

-- Seed Sample Financial Goals for Demo User
INSERT INTO `financial_goals` (`user_id`, `title`, `target_amount`, `current_amount`, `target_date`, `description`, `status`) VALUES
(2, 'High Performance Laptop', 250000.00, 150000.00, CURRENT_DATE + INTERVAL 90 DAY, 'Savings for a modern workstation laptop for IT projects.', 'active'),
(2, 'Emergency Fund', 500000.00, 320000.00, CURRENT_DATE + INTERVAL 180 DAY, '6-month living expenses emergency reserve fund.', 'active'),
(2, 'IT Certification Exam', 50000.00, 50000.00, CURRENT_DATE - INTERVAL 10 DAY, 'Cloud Architecture Certification exam fee.', 'completed');

-- Seed Activity Logs
INSERT INTO `system_activity_logs` (`user_id`, `activity`) VALUES
(1, 'System initialized with default database schema.'),
(2, 'Registered user account Alex Johnson.'),
(2, 'Created monthly budget allocations.');
