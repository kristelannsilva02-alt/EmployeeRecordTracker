-- ============================================================
-- Project: Employee Record Tracker
-- Course: PHP and MySQL Development (Web Systems Technologies)
-- Database Schema & Sample Data
-- ============================================================
-- NOTE FOR XAMPP:
-- 1. Open http://localhost/phpmyadmin
-- 2. Create a new database named `employee_tracker_db`
-- 3. Select `employee_tracker_db` and click the "Import" tab
-- 4. Choose this `database.sql` file and click "Import"
--
-- NOTE FOR INFINITYFREE:
-- 1. Create a database in Control Panel -> MySQL Databases
-- 2. Open phpMyAdmin for that database and import this file directly
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";

-- --------------------------------------------------------
-- Table structure for table `users` (System Login)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `users` (`username`, `password`) VALUES
('kristel', 'stelacy16');

-- --------------------------------------------------------
-- Table structure for table `employees` (10 fields + Primary Key)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `department` varchar(60) NOT NULL,
  `position` varchar(80) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hire_date` date NOT NULL,
  `status` enum('Active','On Leave','Probation','Resigned') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_code` (`employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Sample data for table `employees`
-- --------------------------------------------------------

INSERT INTO `employees` (`employee_code`, `full_name`, `email`, `phone`, `department`, `position`, `salary`, `hire_date`, `status`) VALUES
('EMP-2026-001', 'Maria Clara Santos', 'maria.santos@company.ph', '0917-555-0142', 'Human Resources', 'HR Manager', 52000.00, '2023-03-15', 'Active'),
('EMP-2026-002', 'Juan Miguel Dela Cruz', 'juan.delacruz@company.ph', '0918-555-0198', 'Information Technology', 'Senior Full-Stack Developer', 68500.00, '2022-08-01', 'Active'),
('EMP-2026-003', 'Angela Mae Reyes', 'angela.reyes@company.ph', '0998-555-0231', 'Finance & Accounting', 'Financial Analyst', 44000.00, '2024-01-10', 'Active'),
('EMP-2026-004', 'Carlos Gabriel Mendoza', 'carlos.mendoza@company.ph', '0927-555-0874', 'Operations', 'Operations Supervisor', 48000.00, '2023-11-20', 'On Leave'),
('EMP-2026-005', 'Sophia Lorraine Villanueva', 'sophia.villanueva@company.ph', '0915-555-0619', 'Marketing', 'Digital Marketing Specialist', 36500.00, '2025-02-03', 'Probation'),
('EMP-2026-006', 'Mark Anthony Bautista', 'mark.bautista@company.ph', '0920-555-0349', 'Information Technology', 'Systems Administrator', 49000.00, '2023-06-12', 'Active');

COMMIT;
