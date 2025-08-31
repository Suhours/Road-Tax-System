-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 04:09 PM
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
-- Database: `university_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance`
--

CREATE TABLE `tbl_attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` varchar(100) DEFAULT NULL,
  `attendance_status` enum('Present','Absent') NOT NULL,
  `attendance_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attendance`
--

INSERT INTO `tbl_attendance` (`id`, `student_id`, `full_name`, `course`, `semester`, `attendance_status`, `attendance_date`, `created_at`) VALUES
(1, 83, 'Ahmed Mohomed', '', 'Semester 3 LS', 'Absent', '2025-05-11', '2025-05-11 21:21:24'),
(2, 0, 'Ahmed Mohomed', '', 'Semester 3 LS', 'Present', '2025-05-11', '2025-05-11 21:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance_report`
--

CREATE TABLE `tbl_attendance_report` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `guardian1_name` varchar(100) DEFAULT NULL,
  `guardian1_phone` varchar(20) DEFAULT NULL,
  `guardian2_name` varchar(100) DEFAULT NULL,
  `guardian2_phone` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `reason_student` text DEFAULT NULL,
  `reason_guardian1` text DEFAULT NULL,
  `reason_guardian2` text DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attendance_report`
--

INSERT INTO `tbl_attendance_report` (`id`, `student_id`, `student_name`, `phone`, `semester`, `course`, `guardian1_name`, `guardian1_phone`, `guardian2_name`, `guardian2_phone`, `status`, `reason_student`, `reason_guardian1`, `reason_guardian2`, `recorded_at`) VALUES
(1, 'bcsd00148-23', 'Ahmed Mohomed', '0757641539', 'Semester 3 LS', '', NULL, NULL, '', '', 'Absent', '', '', '', '2025-05-15 11:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_chart_of_accounts`
--

CREATE TABLE `tbl_chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_no` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_course`
--

CREATE TABLE `tbl_course` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equity`
--

CREATE TABLE `tbl_equity` (
  `id` int(11) NOT NULL,
  `equity_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `equity_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_exam_results`
--

CREATE TABLE `tbl_exam_results` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `exam_score` float DEFAULT NULL,
  `final_score` float DEFAULT NULL,
  `attendance_score` float DEFAULT NULL,
  `total_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_exam_results`
--

INSERT INTO `tbl_exam_results` (`id`, `student_id`, `course`, `exam_score`, `final_score`, `attendance_score`, `total_score`) VALUES
(1, 'bcsd00148-23', '', 40, 100, 20, 121),
(2, 'bcsd00148-24', '', 50, 50, 3, 90.15),
(3, 'bcsd00148-23', '', NULL, NULL, NULL, 40);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_exam_structure`
--

CREATE TABLE `tbl_exam_structure` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_exam_structure`
--

INSERT INTO `tbl_exam_structure` (`id`, `type`, `percentage`) VALUES
(1, 'exam', 100),
(2, 'final', 80),
(3, 'attendance', 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expense`
--

CREATE TABLE `tbl_expense` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_faculty`
--

CREATE TABLE `tbl_faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_faculty_dropout`
--

CREATE TABLE `tbl_faculty_dropout` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `faculty_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `head_name` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `dropped_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fixed_assets`
--

CREATE TABLE `tbl_fixed_assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `asset_value` decimal(10,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `condition` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_generate`
--

CREATE TABLE `tbl_generate` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_generate`
--

INSERT INTO `tbl_generate` (`id`, `student_id`, `student_name`, `course`, `semester`, `amount`, `month`, `description`, `created_at`, `status`) VALUES
(1, 'bcsd00148-23', 'Ahmed Mohomed', '', 'Semester 1', 10.00, '2025-05-18 23:11:27', 'lacagti bishan', '2025-05-18 23:11:34', 'pending'),
(2, 'bcsd00148-09', 'hamdi Mohomed jama', '', 'Semester 1', 30.00, '2025-05-26 10:55:53', 'lacag', '2025-05-26 10:56:00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_graduates`
--

CREATE TABLE `tbl_graduates` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `graduate_month` varchar(20) DEFAULT NULL,
  `graduate_year` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_income`
--

CREATE TABLE `tbl_income` (
  `id` int(11) NOT NULL,
  `source` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `income_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_receipt`
--

CREATE TABLE `tbl_receipt` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `ref_number` varchar(100) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `paid_month` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `paid_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_receipt`
--

INSERT INTO `tbl_receipt` (`id`, `student_id`, `ref_number`, `student_name`, `course`, `semester`, `amount_paid`, `paid_month`, `description`, `paid_at`) VALUES
(1, 'bcsd00148-23', '', 'Ahmed Mohomed', '', 'Semester 1', 10.00, '2025-05-18 23:12:12', 'ka qabtey', '2025-05-18 23:12:18');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_salaama_bank`
--

CREATE TABLE `tbl_salaama_bank` (
  `id` int(11) NOT NULL,
  `type` enum('Deposit','Withdraw') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_semester`
--

CREATE TABLE `tbl_semester` (
  `semester_id` int(11) NOT NULL,
  `semester_name` varchar(100) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_share`
--

CREATE TABLE `tbl_share` (
  `id` int(11) NOT NULL,
  `share_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_share_generate`
--

CREATE TABLE `tbl_share_generate` (
  `id` int(11) NOT NULL,
  `share_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_name` varchar(20) DEFAULT NULL,
  `year_name` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_share_paid`
--

CREATE TABLE `tbl_share_paid` (
  `id` int(11) NOT NULL,
  `share_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `month` varchar(20) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_share_receipt`
--

CREATE TABLE `tbl_share_receipt` (
  `id` int(11) NOT NULL,
  `share_name` varchar(100) DEFAULT NULL,
  `share_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff`
--

CREATE TABLE `tbl_staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_generate`
--

CREATE TABLE `tbl_staff_generate` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_name` varchar(20) DEFAULT NULL,
  `year_name` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `generated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_staff_generate`
--

INSERT INTO `tbl_staff_generate` (`id`, `staff_id`, `name`, `faculty`, `amount`, `month_name`, `year_name`, `description`, `created_at`, `status`, `generated_at`) VALUES
(1, '101', 'Ahmed Mohomed', 'ict', 40.00, NULL, NULL, 'lacag', '2025-05-20 19:05:42', 'pending', '2025-05-20 22:05:36'),
(2, '101', 'Ahmed Mohomed', 'ict', 50.00, NULL, NULL, 'lacag', '2025-05-20 19:06:30', 'pending', '2025-05-20 22:06:22'),
(3, '11', 'abdinasir mohamed', 'ict', 100.00, NULL, NULL, 'bile', '2025-05-22 20:25:18', 'pending', '2025-05-22 23:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_receipt`
--

CREATE TABLE `tbl_staff_receipt` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_staff_receipt`
--

INSERT INTO `tbl_staff_receipt` (`id`, `staff_id`, `amount`, `receipt_date`, `description`, `created_at`, `name`, `faculty`, `department`) VALUES
(1, '101', 40.00, NULL, 'lacag', '2025-05-20 19:12:45', 'Ahmed Mohomed', 'ict', '4'),
(2, '11', 50.00, NULL, 'lacag', '2025-05-22 20:25:39', 'abdinasir mohamed', 'ict', '4');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_dropout`
--

CREATE TABLE `tbl_university_dropout` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `date` date NOT NULL,
  `name` varchar(100) NOT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `study_type` varchar(50) DEFAULT NULL,
  `batch` varchar(50) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `status` int(1) NOT NULL,
  `school_name` varchar(100) DEFAULT NULL,
  `school_exam_id` varchar(50) DEFAULT NULL,
  `school_year` varchar(10) DEFAULT NULL,
  `school_point` varchar(10) DEFAULT NULL,
  `college_name` varchar(100) DEFAULT NULL,
  `college_exam_id` varchar(50) DEFAULT NULL,
  `college_year` varchar(10) DEFAULT NULL,
  `college_point` varchar(10) DEFAULT NULL,
  `guardian_relation` varchar(50) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_occupation` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `certificate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_dropout`
--

INSERT INTO `tbl_university_dropout` (`id`, `student_id`, `date`, `name`, `mother_name`, `phone`, `email`, `gender`, `dob`, `admission_date`, `study_type`, `batch`, `shift`, `program`, `academic_year`, `semester`, `section`, `status`, `school_name`, `school_exam_id`, `school_year`, `school_point`, `college_name`, `college_exam_id`, `college_year`, `college_point`, `guardian_relation`, `guardian_name`, `guardian_occupation`, `guardian_phone`, `guardian_address`, `photo`, `certificate`) VALUES
(83, 'bcsd00148-23', '2025-05-10', 'Ahmed Mohomed', 'naciima daahir', '0757641539', 'abdinaasirmaxamedjama252@gmail.com', 'Male', '2025-05-01', '2025-05-15', 'Distance', '2024L', 'All LSC', 'Nursing', '2023 - 2024L', 'Semester 3 LS', 'All', 0, 'ileys', '500', '2022', 'b+', 'sahalsoftware', '500', '2021', 'b-', 'mother', 'Ahmed Mohomed', 'ganacsade', '0757641539', '6street', 'uploads/681f5f6e1f932_ddd.PNG', 'uploads/681f5f6e1f940_Black Simple Daily Motivation Facebook Post.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_employee`
--

CREATE TABLE `tbl_university_employee` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `status` enum('1','2','3') DEFAULT '1',
  `position` varchar(100) DEFAULT NULL,
  `registered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_employee`
--

INSERT INTO `tbl_university_employee` (`id`, `staff_id`, `name`, `faculty`, `department`, `phone_number`, `status`, `position`, `registered_at`) VALUES
(109, '101', 'Ahmed Mohomed', 'ict', '4', '252636147356', '1', 'macalin computer', NULL),
(111, '103', 'abdirisaaq jamac', 'ict', '4', '+252636147375', '1', 'macalin computer', '2025-05-18 23:05:16'),
(112, '11', 'abdinasir mohamed', 'ict', '4', '+252636147375', '1', 'macalin computer', '2025-05-22 23:24:42');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_income_statement`
--

CREATE TABLE `tbl_university_income_statement` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_income_type`
--

CREATE TABLE `tbl_university_income_type` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_income_type`
--

INSERT INTO `tbl_university_income_type` (`id`, `type_name`) VALUES
(4001, 'Income Fee'),
(5001, 'Rent Expenses'),
(5002, 'Salary Expenses'),
(5003, 'Electricity Expenses');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_journal`
--

CREATE TABLE `tbl_university_journal` (
  `id` int(11) NOT NULL,
  `journal_date` date DEFAULT NULL,
  `payer_id` int(11) DEFAULT NULL,
  `account_no` varchar(10) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `type` enum('Credit','Debit') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_journal`
--

INSERT INTO `tbl_university_journal` (`id`, `journal_date`, `payer_id`, `account_no`, `account_name`, `type`, `amount`, `description`, `name`, `created_at`) VALUES
(25, '2025-05-21', 101, '3301', 'Equity', 'Credit', 1000.00, 'Lacag', 'maxamed jama sahal', '2025-05-22 20:07:13'),
(26, '2025-05-21', 101, '1001', 'asset', 'Debit', 1000.00, 'Lacag', 'salaama bank', '2025-05-22 20:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_receipt`
--

CREATE TABLE `tbl_university_receipt` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_share`
--

CREATE TABLE `tbl_university_share` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `share_amount` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_share`
--

INSERT INTO `tbl_university_share` (`id`, `name`, `share_amount`, `status`) VALUES
(1, 'Ahmed Mohomed', 2000.00, 1),
(2, 'huda mudalib', 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_student`
--

CREATE TABLE `tbl_university_student` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `date` date NOT NULL,
  `name` varchar(100) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `study_type` varchar(50) DEFAULT NULL,
  `batch` varchar(50) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `status` int(1) NOT NULL,
  `school_name` varchar(100) DEFAULT NULL,
  `school_exam_id` varchar(50) DEFAULT NULL,
  `school_year` varchar(10) DEFAULT NULL,
  `school_point` varchar(10) DEFAULT NULL,
  `college_name` varchar(100) DEFAULT NULL,
  `college_exam_id` varchar(50) DEFAULT NULL,
  `college_year` varchar(10) DEFAULT NULL,
  `college_point` varchar(10) DEFAULT NULL,
  `guardian_relation` varchar(50) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_occupation` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `certificate` varchar(255) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `institute_name` varchar(100) DEFAULT NULL,
  `institute_exam_id` varchar(50) DEFAULT NULL,
  `institute_year` varchar(10) DEFAULT NULL,
  `institute_point` varchar(10) DEFAULT NULL,
  `guardian2_relation` varchar(50) DEFAULT NULL,
  `guardian2_name` varchar(100) DEFAULT NULL,
  `guardian2_occupation` varchar(100) DEFAULT NULL,
  `guardian2_phone` varchar(20) DEFAULT NULL,
  `guardian2_address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_student`
--

INSERT INTO `tbl_university_student` (`id`, `student_id`, `date`, `name`, `course`, `mother_name`, `phone`, `email`, `gender`, `dob`, `admission_date`, `study_type`, `batch`, `shift`, `program`, `academic_year`, `semester`, `section`, `status`, `school_name`, `school_exam_id`, `school_year`, `school_point`, `college_name`, `college_exam_id`, `college_year`, `college_point`, `guardian_relation`, `guardian_name`, `guardian_occupation`, `guardian_phone`, `guardian_address`, `photo`, `certificate`, `faculty`, `institute_name`, `institute_exam_id`, `institute_year`, `institute_point`, `guardian2_relation`, `guardian2_name`, `guardian2_occupation`, `guardian2_phone`, `guardian2_address`, `created_at`) VALUES
(185, 'bcsd00148-23', '2025-05-18', 'Ahmed Mohomed', NULL, 'naciima yusuf', '0757641539', 'abdinaasirmaxamedjama252@gmail.com', 'Male', '2025-05-27', NULL, 'Regular', '2022', NULL, 'Computer Science', '2021 - 2022', 'Semester 1', NULL, 1, 'ileys', '500', '2022', 'b+', '', '', '', '', 'aabe', 'Ahmed Mohomed', 'ganacsade', '0757641539', '6street', 'uploads/blurr.jpg', 'uploads/eee.PNG', NULL, '', '', '', '', 'hooyo', 'Ahmed Mohomed', 'guri joog', '0757641539', '6street', '2025-05-18 12:44:47'),
(186, 'bcsd00148-09', '2025-05-18', 'hamdi Mohomed jama', NULL, 'hinda ali jaamac', '0757641539', 'abdinaasirmaxamedjama252@gmail.com', 'Female', '2025-05-21', NULL, 'Regular', '2022', NULL, 'Computer Science', '2022 - 2023', 'Semester 1', NULL, 1, 'ileys', '500', '2022', 'b+', '', '', '', '', 'aabe', 'Ahmed Mohomed', 'ganacsade', '0757641539', '6street', 'uploads/Black and Beige Minimal Modern Bold Typography Clothing Brand Logo.png', 'uploads/Capture.PNG', NULL, '', '', '', '', 'hooyo', 'hinda Mohomed', 'guri joog', '0757641539', '6street', '2025-05-18 23:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_university_vendor`
--

CREATE TABLE `tbl_university_vendor` (
  `id` int(11) NOT NULL,
  `vendor_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_university_vendor`
--

INSERT INTO `tbl_university_vendor` (`id`, `vendor_id`, `name`, `phone`, `email`, `status`, `created_at`) VALUES
(1, 'VNDR-001', ' moha', '252634496857', NULL, 1, '2025-05-16 05:18:04'),
(2, 'VNDR-002', 'abdinasir arab', '252634496857', NULL, 1, '2025-05-16 05:18:04'),
(3, NULL, 'abdilaahi jama', '252634496857', NULL, 1, '2025-05-16 05:18:04'),
(4, NULL, 'suheyb jama', '252634496856', NULL, 1, '2025-05-16 05:18:04'),
(5, NULL, 'Ahmed Mohomed', '0757641539', 'abdinaasirmaxamedjama252@gmail.com', NULL, '2025-05-16 05:30:09'),
(6, '40', 'jaama faarax', '0757641539', 'abdinaasirmaxamedjama252@gmail.com', NULL, '2025-05-16 05:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('Admin','User') DEFAULT 'User',
  `reset_token` text DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `email`, `password`, `role`, `reset_token`, `reset_token_expiry`, `status`) VALUES
(1, 'user', '', '$2y$10$XBv/Rir8hxBq1pLFbH3CEuryvF/ODf5DqXV60zaa7bIgx7cSDqEHi', 'User', NULL, NULL, 'active'),
(2, 'admin1', 'abdinaasirmaxamedjama252@gmail.com', '$2y$10$3lqc/BiHh2EFwbr0kIoByO8Vq0OGTYMJrmbcnXLfRUfhvwD5q0Ykm', 'Admin', NULL, NULL, 'active'),
(4, 'saynab', '', '$2y$10$MNqNKZUlIzCMFg5GALetjeyCiCHzmRnHDJSG5UqUmfEjFB3MwJcFS', 'User', NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_pages`
--

CREATE TABLE `tbl_user_pages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_pages`
--

INSERT INTO `tbl_user_pages` (`id`, `user_id`, `page_name`) VALUES
(6, 1, 'Form.php'),
(7, 1, 'edit.php');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_vendors`
--

CREATE TABLE `tbl_vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_vendor_generate`
--

CREATE TABLE `tbl_vendor_generate` (
  `id` int(11) NOT NULL,
  `vendor_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_name` varchar(20) DEFAULT NULL,
  `year_name` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending',
  `generated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_vendor_receipt`
--

CREATE TABLE `tbl_vendor_receipt` (
  `id` int(11) NOT NULL,
  `vendor_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_vendor_receipt`
--

INSERT INTO `tbl_vendor_receipt` (`id`, `vendor_id`, `amount`, `payment_date`, `description`, `created_at`) VALUES
(1, '101', 20.00, '2025-04-30', 'lacag', '2025-05-20 19:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `university_graduate_fee_details`
--

CREATE TABLE `university_graduate_fee_details` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_graduate_fee_details`
--

INSERT INTO `university_graduate_fee_details` (`id`, `student_id`, `amount`, `date`) VALUES
(1, 10, 20.00, '2022-02-02'),
(2, 22, 50.00, '2025-01-01'),
(3, 65, 50.00, '2022-01-01'),
(4, 13, 15.00, '2025-01-01'),
(5, 13, 15.00, '2025-01-01'),
(6, 10, 30.00, '2022-01-01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_attendance_report`
--
ALTER TABLE `tbl_attendance_report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_chart_of_accounts`
--
ALTER TABLE `tbl_chart_of_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_course`
--
ALTER TABLE `tbl_course`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `tbl_equity`
--
ALTER TABLE `tbl_equity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_exam_results`
--
ALTER TABLE `tbl_exam_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_exam_structure`
--
ALTER TABLE `tbl_exam_structure`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_expense`
--
ALTER TABLE `tbl_expense`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_faculty`
--
ALTER TABLE `tbl_faculty`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `tbl_faculty_dropout`
--
ALTER TABLE `tbl_faculty_dropout`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_fixed_assets`
--
ALTER TABLE `tbl_fixed_assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_generate`
--
ALTER TABLE `tbl_generate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_graduates`
--
ALTER TABLE `tbl_graduates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_income`
--
ALTER TABLE `tbl_income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_receipt`
--
ALTER TABLE `tbl_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_salaama_bank`
--
ALTER TABLE `tbl_salaama_bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_semester`
--
ALTER TABLE `tbl_semester`
  ADD PRIMARY KEY (`semester_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `tbl_share`
--
ALTER TABLE `tbl_share`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `share_id` (`share_id`);

--
-- Indexes for table `tbl_share_generate`
--
ALTER TABLE `tbl_share_generate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_share_paid`
--
ALTER TABLE `tbl_share_paid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_share_receipt`
--
ALTER TABLE `tbl_share_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_staff`
--
ALTER TABLE `tbl_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_staff_generate`
--
ALTER TABLE `tbl_staff_generate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_staff_receipt`
--
ALTER TABLE `tbl_staff_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_dropout`
--
ALTER TABLE `tbl_university_dropout`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_employee`
--
ALTER TABLE `tbl_university_employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_income_statement`
--
ALTER TABLE `tbl_university_income_statement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `tbl_university_income_type`
--
ALTER TABLE `tbl_university_income_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_journal`
--
ALTER TABLE `tbl_university_journal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_receipt`
--
ALTER TABLE `tbl_university_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_share`
--
ALTER TABLE `tbl_university_share`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_student`
--
ALTER TABLE `tbl_university_student`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_university_vendor`
--
ALTER TABLE `tbl_university_vendor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_vendors`
--
ALTER TABLE `tbl_vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_vendor_generate`
--
ALTER TABLE `tbl_vendor_generate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_vendor_receipt`
--
ALTER TABLE `tbl_vendor_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `university_graduate_fee_details`
--
ALTER TABLE `university_graduate_fee_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_attendance_report`
--
ALTER TABLE `tbl_attendance_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_chart_of_accounts`
--
ALTER TABLE `tbl_chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_course`
--
ALTER TABLE `tbl_course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_equity`
--
ALTER TABLE `tbl_equity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_exam_results`
--
ALTER TABLE `tbl_exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_exam_structure`
--
ALTER TABLE `tbl_exam_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_expense`
--
ALTER TABLE `tbl_expense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_faculty`
--
ALTER TABLE `tbl_faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_faculty_dropout`
--
ALTER TABLE `tbl_faculty_dropout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fixed_assets`
--
ALTER TABLE `tbl_fixed_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_generate`
--
ALTER TABLE `tbl_generate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_graduates`
--
ALTER TABLE `tbl_graduates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_income`
--
ALTER TABLE `tbl_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_receipt`
--
ALTER TABLE `tbl_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_salaama_bank`
--
ALTER TABLE `tbl_salaama_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_semester`
--
ALTER TABLE `tbl_semester`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_share`
--
ALTER TABLE `tbl_share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_share_generate`
--
ALTER TABLE `tbl_share_generate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_share_paid`
--
ALTER TABLE `tbl_share_paid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_share_receipt`
--
ALTER TABLE `tbl_share_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_staff`
--
ALTER TABLE `tbl_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_staff_generate`
--
ALTER TABLE `tbl_staff_generate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_staff_receipt`
--
ALTER TABLE `tbl_staff_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_university_dropout`
--
ALTER TABLE `tbl_university_dropout`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `tbl_university_employee`
--
ALTER TABLE `tbl_university_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `tbl_university_income_statement`
--
ALTER TABLE `tbl_university_income_statement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_university_journal`
--
ALTER TABLE `tbl_university_journal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_university_receipt`
--
ALTER TABLE `tbl_university_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_university_share`
--
ALTER TABLE `tbl_university_share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_university_student`
--
ALTER TABLE `tbl_university_student`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `tbl_university_vendor`
--
ALTER TABLE `tbl_university_vendor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_vendors`
--
ALTER TABLE `tbl_vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_vendor_generate`
--
ALTER TABLE `tbl_vendor_generate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_vendor_receipt`
--
ALTER TABLE `tbl_vendor_receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `university_graduate_fee_details`
--
ALTER TABLE `university_graduate_fee_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_course`
--
ALTER TABLE `tbl_course`
  ADD CONSTRAINT `tbl_course_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `tbl_semester` (`semester_id`);

--
-- Constraints for table `tbl_semester`
--
ALTER TABLE `tbl_semester`
  ADD CONSTRAINT `tbl_semester_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `tbl_faculty` (`faculty_id`);

--
-- Constraints for table `tbl_university_income_statement`
--
ALTER TABLE `tbl_university_income_statement`
  ADD CONSTRAINT `tbl_university_income_statement_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tbl_university_employee` (`id`),
  ADD CONSTRAINT `tbl_university_income_statement_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `tbl_university_income_type` (`id`);

--
-- Constraints for table `tbl_user_pages`
--
ALTER TABLE `tbl_user_pages`
  ADD CONSTRAINT `tbl_user_pages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
