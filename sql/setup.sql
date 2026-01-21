SET NAMES utf8mb4;
SET
FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Reset Database (Optional: Clear old tables)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `shares`;

-- --------------------------------------------------------
-- 2. Create Users Table
-- Matches app/models/User.php
-- --------------------------------------------------------
CREATE TABLE `users`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `username`   varchar(100) NOT NULL,
    `email`      varchar(150) NOT NULL,
    `password`   varchar(255) NOT NULL,
    `role`       enum('admin','user') DEFAULT 'user',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Create Companies Table
-- Matches app/models/Company.php
-- --------------------------------------------------------
CREATE TABLE `companies`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `name`       varchar(50) NOT NULL,
    `color`      varchar(7)  NOT NULL,
    `cash`       bigint(20) DEFAULT 100000, -- Using BIGINT to match PHP 'int' type
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Create Transactions Table
-- Matches app/models/Transaction.php
-- --------------------------------------------------------
CREATE TABLE `transactions`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `company_id`  int(11) NOT NULL,
    `amount`      int(11) NOT NULL,
    `description` varchar(255) NOT NULL,
    `created_at`  timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY           `company_id` (`company_id`),
    CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Create Shares Table (Portfolio)
-- Matches app/models/Share.php
-- --------------------------------------------------------
CREATE TABLE `shares`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,     -- The company BEING owned
    `owner_id`   int(11) DEFAULT NULL, -- The owner. NULL = THE BANK.
    `amount`     int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_ownership` (`company_id`, `owner_id`),
    CONSTRAINT `fk_share_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_share_owner` FOREIGN KEY (`owner_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Seed Users
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`)
VALUES (1, 'StockMaster', 'admin@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'admin',
        '2026-01-07 10:38:14');

-- --------------------------------------------------------
-- 7. Seed Companies (Defaults)
-- --------------------------------------------------------
INSERT INTO `companies` (`name`, `color`, `cash`)
VALUES ('Haviken', '#ff69b4', 100000),
       ('Spechten', '#198754', 100000),
       ('Sperwers', '#ffc107', 100000),
       ('Zwaluwen', '#0d6efd', 100000),
       ('Valken', '#fd7e14', 100000);

-- --------------------------------------------------------
-- 8. Seed Shares (Initial Portfolio)
-- --------------------------------------------------------
-- Example: 5 Companies. Total 100 shares each.
-- Each company keeps 20 shares of itself.
INSERT INTO `shares` (`company_id`, `owner_id`, `amount`)
SELECT `id`, `id`, 20
FROM `companies`;

-- The Bank (NULL owner) holds the remaining 80 shares for each company
INSERT INTO `shares` (`company_id`, `owner_id`, `amount`)
SELECT `id`, NULL, 80
FROM `companies`;

SET
FOREIGN_KEY_CHECKS = 1;
