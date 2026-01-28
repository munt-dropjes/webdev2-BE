SET NAMES utf8mb4;
SET
FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Reset Database (Optional: Clear old tables)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `task_completions`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `task_categories`;
DROP TABLE IF EXISTS `companies_history`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `shares`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `companies`;

-- --------------------------------------------------------
-- 2. Create Companies Table (MUST BE FIRST)
-- Matches app/models/Company.php
-- --------------------------------------------------------
CREATE TABLE `companies`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `name`       varchar(50) NOT NULL,
    `color`      varchar(7)  NOT NULL,
    `cash`       bigint(20) DEFAULT 100000,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Create Users Table
-- Matches app/models/User.php
-- --------------------------------------------------------
CREATE TABLE `users`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) DEFAULT NULL,
    `username`   varchar(100) NOT NULL,
    `email`      varchar(150) NOT NULL,
    `password`   varchar(255) NOT NULL,
    `role`       enum('admin','user') DEFAULT 'user',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `username` (`username`),
    CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
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
-- Matches app/models/Stock.php
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
-- 6. Create Companies History Table
-- --------------------------------------------------------
CREATE TABLE `companies_history`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `company_id`  int(11) NOT NULL,
    `net_worth`   int(11) NOT NULL,
    `stock_price` int(11) NOT NULL,
    `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY           `idx_recorded_at` (`recorded_at`),
    CONSTRAINT `fk_hist_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Task Categories Table
-- --------------------------------------------------------
CREATE TABLE `task_categories`
(
    `id`        int(11) NOT NULL AUTO_INCREMENT,
    `label`     varchar(100) NOT NULL,
    `reward_p1` int(11) NOT NULL,
    `reward_p2` int(11) NOT NULL,
    `reward_p3` int(11) NOT NULL DEFAULT 0,
    `reward_p4` int(11) NOT NULL DEFAULT 0,
    `reward_p5` int(11) NOT NULL DEFAULT 0,
    `penalty`   int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Tasks Table
-- --------------------------------------------------------
CREATE TABLE `tasks`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `name`        varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_task_cat` FOREIGN KEY (`category_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. Task Completions Table
-- --------------------------------------------------------
CREATE TABLE `task_completions`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `task_id`      int(11) NOT NULL,
    `company_id`   int(11) NOT NULL,
    `completed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_completion` (`task_id`, `company_id`),
    CONSTRAINT `fk_tc_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tc_comp` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 10. Seed Companies
-- --------------------------------------------------------
INSERT INTO `companies` (`name`, `color`, `cash`)
VALUES ('Haviken', '#ff69b4', 100000),
       ('Spechten', '#198754', 100000),
       ('Sperwers', '#ffc107', 100000),
       ('Zwaluwen', '#0d6efd', 100000),
       ('Valken', '#fd7e14', 100000);

-- --------------------------------------------------------
-- 11. Seed Users (Depends on Companies)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`)
VALUES (1, 'StockMaster', 'admin@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'admin',
        '2026-01-07 10:38:14');

INSERT INTO `users` (`company_id`, `username`, `email`, `password`, `role`)
VALUES  (1, 'Haviken', 'haviken@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'user'),
        (2, 'Spechten', 'spechten@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'user'),
        (3, 'Sperwers', 'sperwers@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'user'),
        (4, 'Zwaluwen', 'zwaluwen@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'user'),
        (5, 'Valken', 'valken@game.com', '$2y$12$2NLsHOtVvZIXtew35SmxO.mCkj/.HywBVwfCB3Fflq1F6s0C8ZryK', 'user');

-- --------------------------------------------------------
-- 12. Seed Shares
-- --------------------------------------------------------
-- Example: 5 Companies. Total 100 shares each.
-- A. Own Shares: Each company starts with 25 shares of itself.
INSERT INTO `shares` (`company_id`, `owner_id`, `amount`)
SELECT `id`, `id`, 25
FROM `companies`;

-- B. Cross Ownership: Each company starts with 5 shares of every OTHER company.
INSERT INTO `shares` (`company_id`, `owner_id`, `amount`)
SELECT target.id, owner.id, 5
FROM `companies` target
         JOIN `companies` owner ON target.id != owner.id;

-- C. Bank Shares: The Bank holds the rest (55 shares).
INSERT INTO `shares` (`company_id`, `owner_id`, `amount`)
SELECT `id`, NULL, 55
FROM `companies`;

-- --------------------------------------------------------
-- 13. Seed Task Categories
-- --------------------------------------------------------
INSERT INTO `task_categories` (`label`, `reward_p1`, `reward_p2`, `reward_p3`, `reward_p4`, `reward_p5`)
VALUES ('3e Klasse', 25000, 12500, 5000, -12500, -25000),
       ('2e Klasse', 50000, 25000, 10000, -25000, -50000),
       ('1e Klasse', 100000, 50000, 20000, -50000, -100000),
       ('Algemeen/Overige', 50000, 25000, 10000, -25000, -50000),
       ('Vragen', 5000, 2500, 1000, -2500, -5000);

-- --------------------------------------------------------
-- 14. Seed Tasks
-- --------------------------------------------------------
-- 3e Klasse
INSERT INTO `tasks` (`category_id`, `name`)
    SELECT id, 'Kruissjorring' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, '8-vormige sjorring' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, 'Blokkenstel inscheren' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, 'Paalsteek + Schootsteek' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, 'Bundelsteek' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, 'Hang de vlaggen in de mast' FROM task_categories WHERE label = '3e Klasse' UNION ALL
    SELECT id, 'EHBO' FROM task_categories WHERE label = '3e Klasse';

-- 2e Klasse
INSERT INTO `tasks` (`category_id`, `name`)
    SELECT id, 'Dubbele werpanker' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Diagonaalssjorring' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Vorkssjorring' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Steigerssjorring' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Teruggestoken 8-knoop' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Tonsjorring' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'Coordinaten kruispeiling' FROM task_categories WHERE label = '2e Klasse' UNION ALL
    SELECT id, 'EHBO' FROM task_categories WHERE label = '2e Klasse';

-- 1e Klasse
INSERT INTO `tasks` (`category_id`, `name`)
    SELECT id, 'Oogsplits' FROM task_categories WHERE label = '1e Klasse' UNION ALL
    SELECT id, 'Eindsplits' FROM task_categories WHERE label = '1e Klasse' UNION ALL
    SELECT id, 'Tussensplits' FROM task_categories WHERE label = '1e Klasse' UNION ALL
    SELECT id, 'Turkse knoop' FROM task_categories WHERE label = '1e Klasse' UNION ALL
    SELECT id, '3 op 1 bouwen' FROM task_categories WHERE label = '1e Klasse' UNION ALL
    SELECT id, 'EHBO' FROM task_categories WHERE label = '1e Klasse';

-- Algemeen/Overige
INSERT INTO `tasks` (`category_id`, `name`)
    SELECT id, 'Kaartenhuis 6 verdiepingen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Kruiwagen hout halen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Koffie aan de staf' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, '30 Push-ups' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, '5 Pull-ups' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Gat graven (Welp-formaat)' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Roeien over de oprit' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Plunjezak slepen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Verkenner tillen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Blauwe boekje uitleggen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Handtekening Welpenleiding' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Klasse eis/insigne behalen' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Kidnap PL' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Vissersknoop' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Lassoknoop' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Bindersknoop' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Katteklauw' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Beksteek' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Eindsplits achter de rug' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Oogsplits achter de rug' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Schildknoop' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Chirurgenknoop' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Franse paalsteek' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Hijs zeilen in toren' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Dubbele hielingsteek' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Treksteek' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Maak ontstekingsmechanisme' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Regel plintentrappetje' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Maak luchtballon' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Ideale patrouille indeling' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Brug zonder touw' FROM task_categories WHERE label = 'Algemeen/Overige' UNION ALL
    SELECT id, 'Plattegrond terrein' FROM task_categories WHERE label = 'Algemeen/Overige';

-- Vragen
INSERT INTO `tasks` (`category_id`, `name`)
    SELECT id, 'Naam Groep 3 (1911)' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Hopman Grijze Driehoek' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Oprichting Camerons' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Locatie 1933-1971' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Jamboree 1937' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Naamgeving Camerons' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Daskleur vroeger' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Oudste patrouille naam' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Naam Kaderpatrouille' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Betekenis D.N.C.' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Oude patrouille namen' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Naam Stam 1945' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Naam Stam 1950' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Zomerkamp 1954' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Bouw Spechtenoog' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Andere naam Spechtenoog' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Krentenbrood traditie' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Pavaqua 1957' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Eerste paraboloide NL' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'De Olifant (1960)' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Namen clubbladen' FROM task_categories WHERE label = 'Vragen' UNION ALL
    SELECT id, 'Sperwers winst LSW' FROM task_categories WHERE label = 'Vragen';

SET FOREIGN_KEY_CHECKS = 1;
