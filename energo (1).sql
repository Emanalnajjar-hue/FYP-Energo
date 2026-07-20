-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: 07 يوليو 2026 الساعة 18:00
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `energo`
--

-- --------------------------------------------------------

--
-- بنية الجدول `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  `qr_token` varchar(64) DEFAULT NULL,
  `picked_up_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `equipment_id`, `pickup_date`, `return_date`, `total_amount`, `status`, `qr_token`, `picked_up_at`, `created_at`) VALUES
(39, 6, 34, '2026-07-14', '2026-07-15', 90.00, 'active', '8c59a6f0c7ab9c4ec5e7dd4d19dd771843cfe4901798baabb50121126a49d4db', '2026-07-05 11:56:38', '2026-07-05 08:56:12'),
(40, 6, 34, '2026-07-05', '2026-07-06', 90.00, 'confirmed', '83097ff9721a0c8686fd8716d8bf9efffb48ae8da6527ffcd101d7d03a8285f9', NULL, '2026-07-05 09:48:39');

-- --------------------------------------------------------

--
-- بنية الجدول `delivery_requests`
--

CREATE TABLE `delivery_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `primary_phone` varchar(50) NOT NULL,
  `alt_phone` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `governorate` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `delivery_requests`
--

INSERT INTO `delivery_requests` (`request_id`, `user_id`, `booking_id`, `full_name`, `primary_phone`, `alt_phone`, `address`, `governorate`, `notes`, `status`, `created_at`) VALUES
(4, 6, NULL, 'farah shurrab', '0567917402', '05999999', 'hai-eldaraj', 'north_gaza', 'jdfjsgfuegrue', 'pending', '2026-06-27 19:13:54');

-- --------------------------------------------------------

--
-- بنية الجدول `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT 'default-equipment.jpg',
  `weight_kg` varchar(20) DEFAULT NULL,
  `voltage` varchar(50) DEFAULT NULL,
  `status` enum('available','booked','under_maintenance') NOT NULL DEFAULT 'available',
  `location` enum('north','middle','south') DEFAULT 'north',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_featured` tinyint(1) DEFAULT 0,
  `category` enum('solar','battery','generator','kit','cable','lighting') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `description`, `price_per_day`, `image_url`, `weight_kg`, `voltage`, `status`, `location`, `created_at`, `is_featured`, `category`) VALUES
(1, 'PowerMax Moto', 'A powerful motor with high performance and strong reliability for all applications.', 150.00, 'prod-powermax.jpg', '90Kg', '220V / 360V', 'available', 'north', '2026-05-26 13:45:34', 0, 'generator'),
(2, 'ToraoPower Motor', 'A fast and powerful motor designed for high performance and quick response, with long-lasting durability.', 100.00, 'prod-turbopower.jpg', '45Kg', '250V / 680V', 'available', 'north', '2026-05-26 13:45:34', 0, 'generator'),
(3, 'UltraTV Power Battery', 'A high-performance backup battery for televisions, delivering stable voltage and long operating time.', 10.00, 'prod-ultratv.jpg', '3Kg', '20V / 80V', 'available', 'middle', '2026-05-26 13:45:34', 0, 'battery'),
(4, 'Pro Screen Battery', 'An energy-efficient battery specially made for TVs and small electronic devices.', 200.00, 'prod-ecoscreen.jpg', '50Kg', '220V / 580V', 'available', 'south', '2026-05-26 13:45:34', 0, 'battery'),
(6, 'Lithium Battery 1000W', 'High performance lithium battery for small to medium loads.', 15.00, 'lithium-1000.png', '10Kg', '1000W', 'available', 'north', '2026-05-31 14:49:01', 0, 'battery'),
(7, 'Lithium Battery 2000W', 'Reliable lithium battery suitable for medium load requirements.', 25.00, 'lithium-2000.png', '20Kg', '2000W', 'available', 'middle', '2026-05-31 14:49:01', 0, 'battery'),
(8, 'Lithium Battery 3000W', 'Heavy duty lithium battery for continuous power supply.', 35.00, 'lithium-3000.png', '30Kg', '3000W', 'available', 'south', '2026-05-31 14:49:01', 0, 'battery'),
(9, 'Gas Gen 2500W', 'Portable gas generator, perfect for outdoor and home use.', 45.00, 'gas-gen-2500.png', '45Kg', '2500W', 'available', 'middle', '2026-05-31 14:49:01', 0, 'generator'),
(10, 'Gas Gen 5000W', 'Heavy duty gas generator for large power needs.', 75.00, 'gas-gen-5000.png', '90Kg', '5000W', 'available', 'middle', '2026-05-31 14:49:01', 0, 'generator'),
(11, 'Power Cables Set', 'Complete set of durable power cables and connectors.', 5.00, 'power-cables.png', '5Kg', 'N/A', 'available', 'south', '2026-05-31 14:49:01', 0, 'cable'),
(12, 'Power Cable (5m)', 'Durable 5-meter power cable for secure connections.', 2.00, 'acc1.png', '1.5Kg', 'N/A', 'available', 'north', '2026-06-01 09:46:44', 0, 'cable'),
(13, 'Extension Cord (10m)', '10-meter extension cord for wider reach.', 1.50, 'acc2.png', '2.0Kg', 'N/A', 'booked', 'middle', '2026-06-01 09:46:44', 0, 'generator'),
(14, 'MC4 Solar Connector', 'Standard MC4 connector for solar panel setups.', 1.00, 'acc3.png', '0.2Kg', 'N/A', 'available', 'south', '2026-06-01 09:46:44', 0, 'generator'),
(15, 'Adapter Plug', 'Universal adapter plug for various outlets.', 0.75, 'acc4.png', '0.1Kg', 'N/A', 'available', 'north', '2026-06-01 09:46:44', 0, 'generator'),
(16, 'Lithium Battery', 'High performance storage for your home.', 90.00, 'pro1.jpg', '15kg', '12V', 'available', 'north', '2026-06-08 20:14:05', 1, 'battery'),
(17, 'Solar Generator', 'Reliable power for daily needs.', 150.00, 'pro2.png', '25kg', '220V', 'available', 'north', '2026-06-08 20:14:05', 1, 'generator'),
(18, 'Solar Motor', 'Advanced motor for energy efficiency.', 250.00, 'pro3.png', '10kg', '24V', 'available', 'north', '2026-06-08 20:14:05', 1, 'generator'),
(19, 'Maintenance Kit', 'All-in-one setup and repair tools.', 90.00, 'pro4.png', '5kg', '110V', 'available', 'north', '2026-06-08 20:14:05', 1, 'kit'),
(20, 'Universal Toolkits', 'Essential tools for maintenance and setup.', 10.00, 'toolkit.png', '8kg', '220V', 'available', 'north', '2026-06-08 20:14:17', 0, 'kit'),
(21, 'Digital Monitor', 'High precision digital voltage monitor.', 8.00, 'monitor.png', '1kg', '12V-24V', 'available', 'north', '2026-06-08 20:14:17', 0, 'cable'),
(22, 'Charger', 'Fast battery charger unit.', 20.00, 'ch1.png', '2kg', '12V', 'available', 'north', '2026-06-08 20:14:17', 0, 'cable'),
(23, 'Charger Unit', 'Heavy duty industrial charger unit.', 50.00, 'ch2.png', '10kg', '24V', 'under_maintenance', 'north', '2026-06-08 20:14:17', 0, 'cable'),
(24, 'Heavy-Duty Cables', 'Industrial grade power cables.', 5.00, 'cables.png', '15kg', '220V', 'available', 'north', '2026-06-08 20:14:17', 0, 'cable'),
(25, 'Partable Generator', 'Compact portable generator for outdoor use.', 25.00, 'generator_portable.png', '20kg', '220V', 'available', 'north', '2026-06-08 20:14:17', 1, 'generator'),
(26, 'Green Power Station', 'Eco-friendly solar power station.', 20.00, 'power_station_green.png', '18kg', '110V', 'available', 'middle', '2026-06-08 20:14:17', 1, 'generator'),
(27, 'Pro Power Station', 'Professional high capacity power station.', 30.00, 'power_station_pro.png', '30kg', '220V', 'available', 'north', '2026-06-08 20:14:17', 0, 'battery'),
(28, 'Compact Inverter', 'Space saving power inverter.', 15.00, 'inverter.png', '5kg', '12V', 'available', 'north', '2026-06-08 20:14:17', 0, 'battery'),
(29, 'Lithium Cells', 'Raw lithium cells for battery assembly.', 4.00, 'lithium_cells.png', '1kg', '3.7V', 'available', 'north', '2026-06-08 20:14:17', 0, 'battery'),
(30, 'Home Power Battery', 'Residential power storage battery.', 25.00, 'battery_home.png', '25kg', '48V', 'available', 'north', '2026-06-08 20:14:17', 0, 'battery'),
(31, 'Lithium Battery', 'High performance storage for your home.', 90.00, 'pro1.jpg', '15kg', '12V', 'available', 'north', '2026-06-15 10:27:32', 1, 'battery'),
(32, 'Solar Generator', 'Reliable power for daily needs.', 150.00, 'pro2.png', '20kg', '220V', 'available', 'north', '2026-06-15 10:27:32', 1, 'generator'),
(33, 'Solar Motor', 'Advanced motor for energy efficiency.', 250.00, 'pro3.png', '25kg', '24V', 'available', 'north', '2026-06-15 10:27:32', 1, 'generator'),
(34, 'Maintenance Kit', 'All-in-one setup and repair tools.', 90.00, 'pro4.png', '5kg', '', 'booked', 'north', '2026-06-15 10:27:32', 1, 'kit'),
(35, 'LED Power Light', 'Bright LED lights for your workspace.', 15.00, 'pro-sale3.png', NULL, NULL, 'available', 'north', '2026-06-15 16:31:16', 0, 'lighting'),
(36, 'LED Power Light', 'Portable LED lighting kit.', 15.00, 'pro-sale4.png', '', '', 'available', 'north', '2026-06-15 16:31:16', 0, 'lighting');

-- --------------------------------------------------------

--
-- بنية الجدول `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `issue_type` varchar(100) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `testimonials`
--

CREATE TABLE `testimonials` (
  `testimonial_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `feedback_text` text NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.jpg',
  `password` varchar(255) NOT NULL,
  `user_role` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `dob`, `address`, `city`, `avatar`, `password`, `user_role`, `created_at`) VALUES
(6, 'farah', 'shurrab', 'fmsh_2003@hotmail.com', '0567917402', '2003-09-03', 'hai-eldaraj', 'Gaza', '../images/users/user_6_1781953952.jpg', '$2y$10$KHGJEm1SMw/WueILTegNe.gftde2nTR4hdcejaUPCfizxZ47eXU5y', 1, '2026-05-26 13:34:48'),
(8, 'Marah', 'shurrab', 'marah@gmail.com', '0593202895', NULL, NULL, NULL, 'default.jpg', '$2y$10$G1uq.VSppH6kiloVLs9X8uagE/nZ8YoYcVuaj7YyM6JVfTjEha8Aa', 0, '2026-05-30 15:50:21'),
(9, 'Eman', 'Alnajjar', 'eman@gmail.com', '0588888888', NULL, NULL, NULL, 'default.jpg', '$2y$10$l46B4L6Xjs0sevjYUlSexuhC47iBZ5e6GJDJ0sDZK2/KLrak3x6O2', 0, '2026-06-16 14:28:40'),
(11, 'Sara', 'Omar', 'sara@gmail.com', '0533333333', NULL, NULL, NULL, 'default.jpg', '$2y$10$BPz4ZLD4UtKxeJFB0CfSCeFxoC6Dx3F3RuSe1LU5focCzuaJcY4hi', 0, '2026-06-23 07:43:54');

-- --------------------------------------------------------

--
-- بنية الجدول `wallets`
--

CREATE TABLE `wallets` (
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `user_id`, `balance`) VALUES
(1, 6, 997051.80),
(3, 8, 460.00),
(4, 9, 7630.00),
(6, 11, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `delivery_requests`
--
ALTER TABLE `delivery_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`testimonial_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wallet_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `delivery_requests`
--
ALTER TABLE `delivery_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `testimonial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- قيود الجداول `delivery_requests`
--
ALTER TABLE `delivery_requests`
  ADD CONSTRAINT `delivery_requests_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL;

--
-- قيود الجداول `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE;

--
-- قيود الجداول `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- قيود الجداول `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
