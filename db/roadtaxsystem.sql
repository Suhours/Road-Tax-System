-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 04:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `roadtaxsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_requests`
--

CREATE TABLE `password_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblgenerate`
--

CREATE TABLE `tblgenerate` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `vehicletype` varchar(100) DEFAULT NULL,
  `platenumber` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `due_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `amount_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblgenerate`
--

INSERT INTO `tblgenerate` (`id`, `fullname`, `vehicletype`, `platenumber`, `amount`, `status`, `due_date`, `created_at`, `amount_type`) VALUES
(202, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-08-20 00:00:00', '2025-05-20 13:53:15', '3'),
(203, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 30.00, 'completed', '2025-05-20 15:54:59', '2025-05-20 13:54:59', NULL),
(204, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-20 16:57:00', '2025-05-20 13:55:50', '3'),
(205, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-08-28 00:00:00', '2025-05-20 16:59:52', '3');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auto_charges`
--

CREATE TABLE `tbl_auto_charges` (
  `id` int(11) NOT NULL,
  `interval_months` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_charged_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_auto_charges`
--

INSERT INTO `tbl_auto_charges` (`id`, `interval_months`, `amount`, `vehicle_type`, `due_date`, `source`, `created_at`, `last_charged_date`, `next_due_date`) VALUES
(5, 3, 15.00, 'bajaaj', '2026-03-01', 'zaad/zahal/edahab', '2025-05-13 17:55:32', '2025-03-01', '2026-03-01');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_charge_months`
--

CREATE TABLE `tbl_charge_months` (
  `id` int(11) NOT NULL,
  `interval_months` int(11) DEFAULT NULL,
  `month_name` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_charge_months`
--

INSERT INTO `tbl_charge_months` (`id`, `interval_months`, `month_name`) VALUES
(3, 6, 'June'),
(4, 3, 'September');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reciept`
--

CREATE TABLE `tbl_reciept` (
  `id` int(11) NOT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `plate_number` varchar(100) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'On Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_reciept`
--

INSERT INTO `tbl_reciept` (`id`, `vehicle_type`, `plate_number`, `owner`, `amount`, `due_date`, `receipt_image`, `status`) VALUES
(13, 'Baabuurta 14 HP', 'AA123', 'Guuleed Jaamac Nuur', 70.00, '0000-00-00 00:00:00', 'Blue Modern Eye Catching Vlog YouTube Thumbnail.png', 'On Time');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_pages`
--

CREATE TABLE `tbl_user_pages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_pages`
--

INSERT INTO `tbl_user_pages` (`id`, `user_id`, `page_name`) VALUES
(37, 4, 'roadtaxsystem/dashboard/Vehiclestatement.php'),
(58, 2, 'roadtaxsystem/users/form/form_user_report.php'),
(63, 2, 'roadtaxsystem/users/form/form.php'),
(64, 8, 'roadtaxsystem/users/form/form_user_report.php'),
(65, 8, 'roadtaxsystem/users/form/form.php'),
(66, 13, 'roadtaxsystem/dashboard/payment_recording'),
(67, 13, 'roadtaxsystem/reciept/reciept_payment'),
(68, 13, 'roadtaxsystem/dashboard/reports'),
(69, 13, 'roadtaxsystem/reciept/reciept_report'),
(70, 13, 'roadtaxsystem/generate/generate_report'),
(71, 13, 'roadtaxsystem/dashboard/Vehiclestatement');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('Admin','User') NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_requested` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `email`, `password`, `created_at`, `reset_token`, `reset_requested`) VALUES
(1, 'Admin', 'admin', 'calijaamc@gmail.com', '$2y$10$HnJRUwGS8EyU0LXhyfYceuxZG4/SYHT8Sm56qKB5.LcrG6JHfoT3S', '2025-05-04 13:53:07', NULL, 0),
(2, 'User', 'user', 'abdinaasirmaxamedjama252@gmail.com', '$2y$10$g3eyYCMi7RrkvF8qOfFU0.OLzM5Rer06Cyn140yIhNnaSAy8ltVbW', '2025-05-04 14:03:57', '261180', 0),
(4, 'User', 'user2', 'sahalstore24@gmail.com', '$2y$10$GMkxS40JEQ.JHVULYUBTOeJymK2VnalhMZkfqQc4XYnFvDzLzW87S', '2025-05-06 08:05:40', '261180', 0),
(8, 'User', 'user3', NULL, '$2y$10$U6RNlMBaLPllYFzgix9CI.4rcfi6yUMDvdWmMQx1sZJzXBXo6lrWu', '2025-05-07 07:06:44', NULL, 0),
(10, 'User', 'Nuur', 'Nuur@gmail.com', '$2y$10$EM9NWjx4Gtecjt5kVoT5HuiTOzNXdhBiBGmXYSyP5dCB7qY3s.QEe', '2025-05-11 13:17:36', NULL, 0),
(11, 'Admin', 'admin2', 'yuusuffaarax@gmail.com', '$2y$10$i4KQIwPKjDOsdEqaHroEHebzp3j9plV3NWMmSIUr/GGgaAhTAthcS', '2025-05-12 15:45:46', NULL, 0),
(12, 'User', 'Deeq', 'Ahmed@gmail.com', '$2y$10$xahMIHI.IH8ONjpAyz9.deuRN/F8oS8h4UK1NzziKEvV4YKcScSuu', '2025-05-18 17:23:17', NULL, 0),
(13, 'User', 'Ahmed', 'axmed@gmail.com', '$2y$10$6wTvBDSTqvlANVz3bmhZAugzGiTTJQGiP1/Sd7cwjA/kXP2JJtLOO', '2025-05-20 13:59:05', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vehiclemanagement`
--

CREATE TABLE `vehiclemanagement` (
  `id` int(11) NOT NULL,
  `platenumber` varchar(50) NOT NULL,
  `vehicletype` varchar(100) NOT NULL,
  `carname` varchar(100) DEFAULT NULL,
  `owner` varchar(100) NOT NULL,
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mother_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehiclemanagement`
--

INSERT INTO `vehiclemanagement` (`id`, `platenumber`, `vehicletype`, `carname`, `owner`, `registration_date`, `created_at`, `mother_name`, `phone`, `user_id`, `model`) VALUES
(35, 'AA123', 'Baabuurta 14 HP', 'Baabuurta 14 HP', 'Guuleed Jaamac Nuur', '2025-05-20', '2025-05-20 13:53:15', NULL, '252634039214', 1, '2009'),
(36, '125678', 'Baabuurta 13-24 Ton', 'Baabuurta 13-24 Ton', 'jaamac diile', '2025-05-28', '2025-05-20 16:59:52', NULL, '252636147356', 1, 'ooa1');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `amount_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_types`
--

INSERT INTO `vehicle_types` (`id`, `name`, `amount`, `amount_type`) VALUES
(3, 'bajaaj', 15.00, '3bilood'),
(5, 'Motto', 15.00, '3bilood'),
(6, 'Baabuurta 14 HP', 20.00, '3bilood'),
(7, 'Baabuurta 18 HP', 25.00, '3bilood'),
(8, 'Baabuurta 21 HP', 30.00, '3bilood'),
(9, 'Baabuurta 24 HP', 35.00, '3bilood'),
(10, 'aabuurta 1 Ton', 20.00, '3bilood'),
(11, 'Baabuurta 2 Ton', 25.00, '3bilood'),
(12, 'Baabuurta 3 Ton', 30.00, '3bilood'),
(13, 'Baabuurta 4 Ton', 40.00, '3bilood'),
(14, 'Baabuurta 5-6 Ton', 50.00, '3bilood'),
(15, 'Baabuurta 7-12 Ton', 70.00, '3bilood'),
(16, 'Baabuurta 13-24 Ton', 75.00, '3bilood'),
(17, 'Baabuurta 25-36 Ton', 100.00, '3bilood'),
(18, 'Baabuurta 37-70 Ton', 110.00, '3bilood'),
(19, 'Baabuurta 70-ton iyo kabadan', 120.00, '3bilood');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_requests`
--
ALTER TABLE `password_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblgenerate`
--
ALTER TABLE `tblgenerate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auto_charges`
--
ALTER TABLE `tbl_auto_charges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_charge_months`
--
ALTER TABLE `tbl_charge_months`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_reciept`
--
ALTER TABLE `tbl_reciept`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vehiclemanagement`
--
ALTER TABLE `vehiclemanagement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `password_requests`
--
ALTER TABLE `password_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblgenerate`
--
ALTER TABLE `tblgenerate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `tbl_auto_charges`
--
ALTER TABLE `tbl_auto_charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_charge_months`
--
ALTER TABLE `tbl_charge_months`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_reciept`
--
ALTER TABLE `tbl_reciept`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vehiclemanagement`
--
ALTER TABLE `vehiclemanagement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
