-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 06:54 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `make_vehicle_payment` (IN `p_number_plate` VARCHAR(50) CHARSET utf8, IN `p_amount_paid` DECIMAL(11,2), IN `p_description` TEXT CHARSET utf8)   BEGIN
  DECLARE unpaid_balance DECIMAL(10,2);
  DECLARE tax_per_quarter DECIMAL(10,2);
  DECLARE last_paid DATE;
  DECLARE vehicle_type_id INT;
  DECLARE full_quarters_due INT;
  DECLARE quarters_paid INT;
  DECLARE current_quarter_start DATE;
  DECLARE last_quarter_index INT;
  DECLARE current_quarter_index INT;
  DECLARE year_diff INT;

  -- Define quarter start months: 1 (Jan), 4 (Apr), 7 (Jul), 10 (Oct)

  -- Get vehicle type ID
  SELECT v.vehicle_type_id INTO vehicle_type_id
  FROM vehicles v
  WHERE v.number_plate = p_number_plate;

  -- Get tax per quarter
  SELECT vt.amount_per_three_month INTO tax_per_quarter
  FROM vehicle_type vt
  WHERE vt.id = vehicle_type_id;

  -- Get balance and last payment date
  SELECT balance, last_payment_date INTO unpaid_balance, last_paid
  FROM vehicle_balance
  WHERE number_plate = p_number_plate;

  -- Compute last quarter index (1 to 4)
  SET last_quarter_index = CASE 
    WHEN MONTH(last_paid) = 1 THEN 1
    WHEN MONTH(last_paid) = 4 THEN 2
    WHEN MONTH(last_paid) = 7 THEN 3
    WHEN MONTH(last_paid) = 10 THEN 4
  END;

  -- Compute current quarter index (1 to 4)
  SET current_quarter_index = CASE 
    WHEN MONTH(CURDATE()) >= 10 THEN 4
    WHEN MONTH(CURDATE()) >= 7 THEN 3
    WHEN MONTH(CURDATE()) >= 4 THEN 2
    ELSE 1
  END;

  SET year_diff = YEAR(CURDATE()) - YEAR(last_paid);

  -- Calculate quarters due purely based on calendar quarters
  SET full_quarters_due = year_diff * 4 + (current_quarter_index - last_quarter_index);

  IF full_quarters_due < 0 THEN
    SET full_quarters_due = 0;
  END IF;

  -- Update unpaid balance with due quarters
  SET unpaid_balance = unpaid_balance + (full_quarters_due * tax_per_quarter);

  -- Calculate how many quarters the payment covers
  SET quarters_paid = FLOOR(p_amount_paid / tax_per_quarter);

  -- Update last payment date only if at least one full quarter is paid
  IF quarters_paid > 0 THEN
    SET last_paid = DATE_ADD(last_paid, INTERVAL quarters_paid * 3 MONTH);
  END IF;

  -- Deduct only the amount paid
  SET unpaid_balance = unpaid_balance - p_amount_paid;

  IF unpaid_balance < 0 THEN
    SET unpaid_balance = 0;
  END IF;

  -- Update balance
  UPDATE vehicle_balance
  SET balance = unpaid_balance,
      last_payment_date = last_paid
  WHERE number_plate = p_number_plate;

  -- Record payment
  INSERT INTO payment_record (number_plate, amount_paid, payment_date, description)
  VALUES (p_number_plate, p_amount_paid, NOW(), p_description);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `get_quarter` (`p_date` DATE) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE q INT;
    SET q = QUARTER(p_date);
    RETURN q;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_quarter_start` (`input_date` DATE) RETURNS DATE DETERMINISTIC BEGIN
    DECLARE quarter_start DATE;

    SET quarter_start = CASE
        WHEN MONTH(input_date) BETWEEN 1 AND 3 THEN DATE_FORMAT(input_date, '%Y-01-01')
        WHEN MONTH(input_date) BETWEEN 4 AND 6 THEN DATE_FORMAT(input_date, '%Y-04-01')
        WHEN MONTH(input_date) BETWEEN 7 AND 9 THEN DATE_FORMAT(input_date, '%Y-07-01')
        ELSE DATE_FORMAT(input_date, '%Y-10-01')
    END;

    RETURN quarter_start;
END$$

DELIMITER ;

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
-- Table structure for table `payment_record`
--

CREATE TABLE `payment_record` (
  `id` int(11) NOT NULL,
  `number_plate` varchar(20) NOT NULL,
  `amount_paid` decimal(11,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_record`
--

INSERT INTO `payment_record` (`id`, `number_plate`, `amount_paid`, `payment_date`, `description`) VALUES
(1, 'GH77', 40.00, '2025-05-29 19:04:48', 'quarter 2'),
(2, 'GH77', 40.00, '2025-05-29 19:15:08', 'quarter 2'),
(3, 'UY66', 20.00, '2025-05-29 19:40:08', 'one quarter');

--
-- Triggers `payment_record`
--
DELIMITER $$
CREATE TRIGGER `after_payment_insert` AFTER INSERT ON `payment_record` FOR EACH ROW BEGIN
  -- Update last_payment_date to match the new payment's payment_date
  UPDATE vehicle_balance
  SET last_payment_date = NEW.payment_date
  WHERE number_plate = NEW.number_plate;
END
$$
DELIMITER ;

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
  `amount_type` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblgenerate`
--

INSERT INTO `tblgenerate` (`id`, `fullname`, `vehicletype`, `platenumber`, `amount`, `status`, `due_date`, `created_at`, `amount_type`, `user_id`) VALUES
(202, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-08-20 00:00:00', '2025-05-20 13:53:15', '3', NULL),
(203, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 30.00, 'completed', '2025-05-20 15:54:59', '2025-05-20 13:54:59', NULL, NULL),
(204, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-20 16:57:00', '2025-05-20 13:55:50', '3', NULL),
(205, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-08-28 00:00:00', '2025-05-20 16:59:52', '3', NULL),
(207, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-06 19:22:00', '2025-05-21 16:22:31', '3', NULL),
(208, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-05-06 19:22:00', '2025-05-21 16:22:31', '3', NULL),
(209, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-04-30 16:21:00', '2025-05-22 13:21:41', '3', NULL),
(210, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-04-30 16:21:00', '2025-05-22 13:21:41', '3', NULL),
(211, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 25.00, 'pending', '2025-04-30 16:21:00', '2025-05-22 13:21:41', '3', NULL),
(212, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 10.00, 'completed', '2025-05-22 15:22:23', '2025-05-22 13:22:23', NULL, NULL),
(213, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-22 16:31:00', '2025-05-22 13:31:34', '3', NULL),
(214, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-05-22 16:31:00', '2025-05-22 13:31:34', '3', NULL),
(215, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 25.00, 'pending', '2025-05-22 16:31:00', '2025-05-22 13:31:34', '3', NULL),
(216, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-22 19:28:00', '2025-05-22 16:28:38', '3', NULL),
(217, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-05-22 19:28:00', '2025-05-22 16:28:38', '3', NULL),
(218, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 25.00, 'pending', '2025-05-22 19:28:00', '2025-05-22 16:28:38', '3', NULL),
(219, 'hasan ali', 'aabuurta 1 Ton', '12909', 20.00, 'pending', '2025-05-22 19:28:00', '2025-05-22 16:28:38', '3', NULL),
(220, 'cali mahamuud cali', 'Baabuurta 14 HP', '00990', 20.00, 'pending', '2025-08-22 00:00:00', '2025-05-22 17:29:22', '3', NULL),
(221, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(222, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(223, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 25.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(224, 'hasan ali', 'aabuurta 1 Ton', '12909', 20.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(225, 'ahmed diile', 'aabuurta 1 Ton', '334455', 20.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(226, 'ahmed diile', 'aabuurta 1 Ton', '112200', 20.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(227, 'cali mahamuud cali', 'Baabuurta 14 HP', '00990', 20.00, 'pending', '2025-05-24 18:06:00', '2025-05-24 15:06:57', '3', NULL),
(228, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 20.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(229, 'jaamac diile', 'Baabuurta 13-24 Ton', '125678', 75.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(230, 'mohamed osman', 'Baabuurta 2 Ton', '0987', 25.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(231, 'hasan ali', 'aabuurta 1 Ton', '12909', 20.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(232, 'ahmed diile', 'aabuurta 1 Ton', '334455', 20.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(233, 'ahmed diile', 'aabuurta 1 Ton', '112200', 20.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(234, 'cali mahamuud cali', 'Baabuurta 14 HP', '00990', 20.00, 'pending', '2025-05-24 18:07:00', '2025-05-24 15:07:05', '6', NULL),
(235, 'cali mahamuud cali', 'Baabuurta 14 HP', '00990', 60.00, 'completed', '2025-05-24 17:50:46', '2025-05-24 15:50:46', NULL, NULL),
(236, 'Guuleed Jaamac Nuur', 'Baabuurta 14 HP', 'AA123', 40.00, 'pending', '2025-05-24 18:51:00', '2025-05-24 15:51:08', '6', NULL),
(237, 'cali mahamuud cali', 'Baabuurta 14 HP', '00990', 40.00, 'pending', '2025-05-24 18:51:00', '2025-05-24 15:51:08', '6', NULL);

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
  `status` varchar(20) DEFAULT 'On Time',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(37, 4, 'dashboard/Vehiclestatement.php'),
(64, 8, 'dashboard/form.php'),
(65, 8, 'dashboard/form.php'),
(66, 13, 'dashboard/payment_recording'),
(67, 13, 'reciept/reciept_payment'),
(68, 13, 'dashboard/reports'),
(69, 13, 'reciept/reciept_report'),
(70, 13, 'generate/generate_report'),
(72, 14, 'dashboard/form.php'),
(73, 14, 'form/form');

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
  `reset_requested` int(11) DEFAULT 0,
  `reset_token_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `email`, `password`, `created_at`, `reset_requested`, `reset_token_expiry`, `reset_token`, `status`) VALUES
(1, 'Admin', 'admin', 'abdinaasirmaxamedjama252@gmail.com', '$2y$10$L8h7V//cKfvQj1UWZUtLu.ZB80yO/mtG46Tq1L41EVxu2E7Gb8T52', '2025-05-04 13:53:07', 0, NULL, NULL, 'active'),
(2, 'User', 'user', 'abdinaasirmaxamedjama252@gmail.com', '$2y$10$gVnwjm24maRfOTme7tUt7ed0LmfLjNlESI9fwv8cUC5uEQ0Uxoo76', '2025-05-04 14:03:57', 0, '2025-05-25 10:15:06', 'mq0s2A87PKfSntCdRtEhArtz36cFK6ystk2I-uXWC5I', 'active'),
(4, 'User', 'user2', 'sahalstore24@gmail.com', '$2y$10$2IKe5t1BP2sxidGaggnTz.xnGnLAnIyZpeMPyv5ZTdYFgO439U4r2', '2025-05-06 08:05:40', 0, '2025-05-25 10:15:30', 'OsNEZu6pWnn5P4mH-4T26MpTo7mJKGHoIzLcLSeSkG0', 'active'),
(8, 'User', 'user3', NULL, '$2y$10$U6RNlMBaLPllYFzgix9CI.4rcfi6yUMDvdWmMQx1sZJzXBXo6lrWu', '2025-05-07 07:06:44', 0, NULL, NULL, 'dropout'),
(10, 'User', 'Nuur', 'Nuur@gmail.com', '$2y$10$EM9NWjx4Gtecjt5kVoT5HuiTOzNXdhBiBGmXYSyP5dCB7qY3s.QEe', '2025-05-11 13:17:36', 0, NULL, NULL, 'active'),
(11, 'Admin', 'admin2', 'yuusuffaarax@gmail.com', '$2y$10$i4KQIwPKjDOsdEqaHroEHebzp3j9plV3NWMmSIUr/GGgaAhTAthcS', '2025-05-12 15:45:46', 0, NULL, NULL, 'active'),
(12, 'User', 'Deeq', 'Ahmed@gmail.com', '$2y$10$xahMIHI.IH8ONjpAyz9.deuRN/F8oS8h4UK1NzziKEvV4YKcScSuu', '2025-05-18 17:23:17', 0, NULL, NULL, 'active'),
(13, 'User', 'Ahmed', 'axmed@gmail.com', '$2y$10$6wTvBDSTqvlANVz3bmhZAugzGiTTJQGiP1/Sd7cwjA/kXP2JJtLOO', '2025-05-20 13:59:05', 0, NULL, NULL, 'active'),
(14, 'User', 'mo123', 'example@gmail.com', '$2y$10$wKmVXNgV65YsqQBdLrrs5OUZShUp9bjRWYQGU/YbvoyZN5mUkVaR2', '2025-05-22 13:24:19', 0, NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `number_plate` varchar(20) NOT NULL,
  `vehicle_type_id` int(11) NOT NULL,
  `carname` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) NOT NULL,
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mother_name` varchar(100) DEFAULT NULL,
  `owner_phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `number_plate`, `vehicle_type_id`, `carname`, `owner_name`, `registration_date`, `created_at`, `mother_name`, `owner_phone`, `user_id`, `model`) VALUES
(48, 'GH77', 13, 'Land Cruiser', 'Mohamed', '2025-05-29', '2025-05-29 15:59:41', NULL, '2838383', NULL, '2008'),
(49, 'UY66', 10, 'V8', 'abdinasir', '2025-05-29', '2025-05-29 16:38:00', NULL, '2838383', NULL, '2011');

--
-- Triggers `vehicles`
--
DELIMITER $$
CREATE TRIGGER `after_vehicle_insert` AFTER INSERT ON `vehicles` FOR EACH ROW BEGIN
  DECLARE tax DECIMAL(10,2);
  DECLARE quarter_start DATE;

  -- Get tax per quarter
  SELECT amount_per_three_month INTO tax
  FROM vehicle_type
  WHERE id = NEW.vehicle_type_id;

  -- Determine current quarter start date
  SET quarter_start = MAKEDATE(YEAR(CURDATE()), 1) + INTERVAL (QUARTER(CURDATE()) - 1) * 3 MONTH;

  -- Insert vehicle balance with initial quarter charge
  INSERT INTO vehicle_balance (number_plate, balance, last_payment_date)
  VALUES (NEW.number_plate, tax, quarter_start);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_balance`
--

CREATE TABLE `vehicle_balance` (
  `number_plate` varchar(100) NOT NULL,
  `balance` decimal(11,2) NOT NULL,
  `last_payment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_balance`
--

INSERT INTO `vehicle_balance` (`number_plate`, `balance`, `last_payment_date`) VALUES
('GH77', 0.00, '2025-03-01'),
('UY66', 0.00, '2025-05-29');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_type`
--

CREATE TABLE `vehicle_type` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `amount_per_three_month` decimal(10,2) DEFAULT NULL,
  `amount_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_type`
--

INSERT INTO `vehicle_type` (`id`, `type`, `amount_per_three_month`, `amount_type`) VALUES
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
-- Indexes for table `payment_record`
--
ALTER TABLE `payment_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_payment` (`number_plate`);

--
-- Indexes for table `tblgenerate`
--
ALTER TABLE `tblgenerate`
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
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `namber_plate` (`number_plate`),
  ADD KEY `vehicle_type` (`vehicle_type_id`);

--
-- Indexes for table `vehicle_balance`
--
ALTER TABLE `vehicle_balance`
  ADD PRIMARY KEY (`number_plate`);

--
-- Indexes for table `vehicle_type`
--
ALTER TABLE `vehicle_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `password_requests`
--
ALTER TABLE `password_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_record`
--
ALTER TABLE `payment_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblgenerate`
--
ALTER TABLE `tblgenerate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `tbl_reciept`
--
ALTER TABLE `tbl_reciept`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `vehicle_type`
--
ALTER TABLE `vehicle_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_record`
--
ALTER TABLE `payment_record`
  ADD CONSTRAINT `vehicle_payment` FOREIGN KEY (`number_plate`) REFERENCES `vehicles` (`number_plate`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicle_type` FOREIGN KEY (`vehicle_type_id`) REFERENCES `vehicle_type` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `vehicle_balance`
--
ALTER TABLE `vehicle_balance`
  ADD CONSTRAINT `vehicle_balance` FOREIGN KEY (`number_plate`) REFERENCES `vehicles` (`number_plate`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
