-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 03, 2025 at 03:49 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maconglomo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `call_logs`
--

CREATE TABLE `call_logs` (
  `id` int NOT NULL,
  `medrep_id` int NOT NULL,
  `date` date NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `precall_notes` text COLLATE utf8mb4_general_ci,
  `postcall_notes` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `call_logs`
--

INSERT INTO `call_logs` (`id`, `medrep_id`, `date`, `client_name`, `precall_notes`, `postcall_notes`, `created_at`) VALUES
(1, 7, '2025-08-15', 'Callie Torres', 'Hello', 'World!', '2025-08-14 17:01:15'),
(2, 7, '2025-08-15', 'Callie Torres', 'Hello', 'World!', '2025-08-14 17:01:35'),
(3, 7, '2025-08-15', 'Dr. Mark Sloan', 'Hello', 'World', '2025-08-14 17:03:35'),
(4, 7, '2025-08-15', 'Dr. Meredith Grey', 'Plane', '', '2025-08-14 17:09:34'),
(5, 7, '2025-08-26', 'Dr. Mark Sloan', 'Omygosh', 'Mark Slon', '2025-08-26 07:21:08'),
(6, 7, '2025-08-27', 'Dra. Faye Garcia', 'To cover Macproto.', 'The cover went well.', '2025-08-27 00:22:34'),
(7, 7, '2025-09-20', 'Dr. Cruz', 'I am going to call about paracetamol.', 'Client was interested', '2025-09-20 13:18:41'),
(8, 7, '2025-09-28', 'Dr. Mark Sloan', 'AWDWAD', 'AWDAWD', '2025-09-27 17:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `client_logs`
--

CREATE TABLE `client_logs` (
  `id` int NOT NULL,
  `medrep_id` int NOT NULL,
  `date` date NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `hospital_clinic` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `products_covered` text COLLATE utf8mb4_general_ci NOT NULL,
  `proof_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_logs`
--

INSERT INTO `client_logs` (`id`, `medrep_id`, `date`, `client_name`, `hospital_clinic`, `products_covered`, `proof_image`, `created_at`) VALUES
(1, 7, '2025-08-15', 'Dr. Meredith Grey', 'Grey Sloan Memorial Hospital', 'Hello World!', 'uploads/medrep_logs/641f328b4c14aece.jpg', '2025-08-14 17:24:46'),
(2, 7, '2025-08-26', 'Dr. Webber', 'Grey Sloan Memorial Hospital', 'HEHE', 'uploads/medrep_logs/94f8c4cd78cc58bd.jpg', '2025-08-26 07:22:49'),
(3, 7, '2025-08-27', 'Angelo Victoria', 'GOODSAM', 'MACPROTO', 'uploads/medrep_logs/6cf43cad2875bfce.webp', '2025-08-27 00:21:14'),
(4, 7, '2025-08-27', 'Dra. Christine Tan', 'PJG', 'AMINOLIFE', 'uploads/medrep_logs/d4944bb43125c422.webp', '2025-08-27 01:05:15'),
(5, 7, '2025-09-20', 'Dr. Victoria', 'CLSU Infirmary', 'PARACETAMOL - Biogesic', 'uploads/medrep_logs/4dd010e9d01eea06.jpg', '2025-09-20 13:20:46'),
(6, 7, '2025-09-28', 'Dra. Faye Garcia', 'GOODSAM', 'AMINOLIFE', 'uploads/medrep_logs/70ff12f033c0324d.png', '2025-09-27 16:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int NOT NULL,
  `generic_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `brand_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `unit` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `generic_name`, `brand_name`, `unit`) VALUES
(7, 'AMINO ACID + SORBITOL', 'Aminolife', 'BOT'),
(13, 'CEFUROXIME (10S)', 'Macproto', 'VIAL'),
(11, 'PANTOPRAZOLE', 'Macproto', 'VIAL'),
(12, 'PARACETAMOL', 'Biogesic', 'TABLET'),
(14, 'PIPERACILLIN + TAZOBACT', 'Aminolife', 'TABLET');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_batches`
--

CREATE TABLE `medicine_batches` (
  `id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `batch_no` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `cost_per_unit` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `stock_on_hand` int NOT NULL DEFAULT '0',
  `cost` decimal(10,2) DEFAULT NULL,
  `received_at` date NOT NULL DEFAULT (curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_batches`
--

INSERT INTO `medicine_batches` (`id`, `medicine_id`, `batch_no`, `expiry_date`, `quantity`, `cost_per_unit`, `created_at`, `stock_on_hand`, `cost`, `received_at`) VALUES
(23, 7, '2000', '2028-06-25', 21000, 500.00, '2025-09-25 03:58:21', 21000, 10500000.00, '2025-09-25'),
(28, 13, '003', '2026-12-25', 4000, NULL, '2025-09-25 05:19:11', 1000, 0.00, '2025-09-25'),
(30, 14, '25042619', '2025-09-02', 1000, NULL, '2025-09-27 14:11:43', 1000, 0.00, '2025-09-27'),
(31, 11, '000005', '2026-05-27', 13, NULL, '2025-09-27 14:13:45', 13, 0.00, '2025-09-27'),
(32, 12, '12213123', '2026-05-27', 1000, NULL, '2025-09-27 15:27:27', 1000, 0.00, '2025-09-27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'InventoryKeeper'),
(3, 'MedRep');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `batch_id` int NOT NULL,
  `transaction_type` enum('IN','OUT') COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL,
  `quantity` int NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `invoice_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `medicine_id`, `batch_id`, `transaction_type`, `date`, `quantity`, `cost`, `price`, `amount`, `invoice_no`, `customer`, `created_at`) VALUES
(13, 7, 23, 'IN', '2025-09-25', 20000, 10000000.00, NULL, NULL, NULL, NULL, '2025-09-25 03:58:21'),
(14, 7, 23, 'IN', '2025-09-25', 1000, 500000.00, NULL, NULL, NULL, NULL, '2025-09-25 03:59:17'),
(22, 13, 28, 'IN', '2025-09-25', 2000, 0.00, NULL, NULL, NULL, NULL, '2025-09-25 05:19:11'),
(23, 13, 28, 'OUT', '2025-09-25', 2000, NULL, NULL, NULL, 'DR-219', 'TMC', '2025-09-25 05:22:26'),
(24, 13, 28, 'IN', '2025-09-25', 2000, 0.00, NULL, NULL, NULL, NULL, '2025-09-25 05:23:18'),
(25, 13, 28, 'OUT', '2025-09-25', 1000, NULL, 572.00, NULL, '1231234', 'GCM', '2025-09-25 05:23:44'),
(27, 14, 30, 'IN', '2025-09-27', 1000, 0.00, NULL, NULL, NULL, NULL, '2025-09-27 14:11:43'),
(28, 11, 31, 'IN', '2025-09-27', 13, 0.00, NULL, NULL, NULL, NULL, '2025-09-27 14:13:45'),
(29, 12, 32, 'IN', '2025-09-27', 1000, 0.00, NULL, NULL, NULL, NULL, '2025-09-27 15:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `is_approved`, `is_active`, `created_at`, `updated_at`, `must_change_password`) VALUES
(1, 1, 'System Administrator', 'jimwelljuliancruz@gmail.com', '$2y$10$0IwSpZh29laq07lBO.g64epU1xu9BhlrBq/1xCgJbhdffXGFbv0Aa', 1, 1, '2025-08-12 01:14:02', '2025-08-14 00:06:51', 0),
(2, 2, 'Aina Patrice Recometa', 'aina.recometa@clsu2.edu.ph', '$2y$10$.mhW0wlAf4rCC5UvXqXkd.TQeGfx34C4OwlkaOe0jsz7kyn/P/ib6', 1, 1, '2025-08-14 00:20:45', '2025-08-14 00:34:19', 0),
(7, 3, 'Angelo Gabriel Victoria', 'angelogabriel.victoria@clsu2.edu.ph', '$2y$10$zeCncmCTxM3nWxmBZZo/5.RrkFmxaoWcdzwuJi9jwY3QXW3EbKjEq', 1, 1, '2025-08-14 02:22:49', '2025-08-14 02:23:01', 0),
(8, 3, 'newmedrep2', 'newmedrep2@gmail.com', '$2y$10$rchCHgHOcsF1Pe/rKp09BOkkNjQjJk6.3fF6f/ubdgnL4041k9PFy', 1, 1, '2025-08-26 15:30:31', '2025-08-26 15:30:48', 0),
(9, 2, 'Rusty Dalit', 'rustydalit@gmail.com', '$2y$10$C/QInhox.4hvD/zbiB1pp.toh0rGnQdU72HAi/7oy4yzGGlcfcIaW', 1, 1, '2025-09-20 21:27:42', '2025-09-20 21:29:23', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_approvals`
--

CREATE TABLE `user_approvals` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` enum('APPROVE','REJECT') COLLATE utf8mb4_general_ci NOT NULL,
  `admin_id` int NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `action_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_approvals`
--

INSERT INTO `user_approvals` (`id`, `user_id`, `action`, `admin_id`, `reason`, `action_at`) VALUES
(6, 7, 'APPROVE', 1, NULL, '2025-08-14 02:23:01'),
(7, 8, 'APPROVE', 1, NULL, '2025-08-26 15:30:48'),
(8, 9, 'APPROVE', 1, NULL, '2025-09-20 21:29:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `call_logs`
--
ALTER TABLE `call_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medrep_id` (`medrep_id`);

--
-- Indexes for table `client_logs`
--
ALTER TABLE `client_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medrep_id` (`medrep_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_medicine` (`generic_name`,`brand_name`,`unit`);

--
-- Indexes for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch_transtype` (`batch_id`,`transaction_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_approvals`
--
ALTER TABLE `user_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_approvals_ibfk_1` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `call_logs`
--
ALTER TABLE `call_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `client_logs`
--
ALTER TABLE `client_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_approvals`
--
ALTER TABLE `user_approvals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `call_logs`
--
ALTER TABLE `call_logs`
  ADD CONSTRAINT `call_logs_ibfk_1` FOREIGN KEY (`medrep_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_logs`
--
ALTER TABLE `client_logs`
  ADD CONSTRAINT `client_logs_ibfk_1` FOREIGN KEY (`medrep_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_batches`
--
ALTER TABLE `medicine_batches`
  ADD CONSTRAINT `medicine_batches_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_approvals`
--
ALTER TABLE `user_approvals`
  ADD CONSTRAINT `user_approvals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_approvals_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
