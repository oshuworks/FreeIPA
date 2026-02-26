-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 03:24 AM
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
-- Database: `freeaypa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `severity` enum('INFO','WARNING','ERROR','CRITICAL') DEFAULT 'INFO',
  `success` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `details`, `ip_address`, `user_agent`, `severity`, `success`, `created_at`) VALUES
(1, 1, 'admin', 'SYSTEM_INIT', NULL, 'Database initialized', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:22:12'),
(2, 1, 'admin', 'LOGIN_SUCCESS', NULL, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:22:43'),
(3, 1, 'admin', 'ROLE_CREATED', NULL, 'Created role: developer', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:23:34'),
(4, 1, 'admin', 'USER_CREATED', NULL, 'Created user: Glorimo Jay Borlasa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:24:49'),
(5, 1, 'admin', 'USER_CREATED', NULL, 'Created user: Yvette Cortez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:28:56'),
(6, 1, 'admin', 'USER_DELETED', NULL, 'Deleted user: Yvette Cortez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:29:08'),
(7, NULL, 'system', 'LOGIN_FAILED', NULL, 'Failed login attempt for username: gjayborlasa@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:31:46'),
(8, NULL, 'system', 'LOGIN_FAILED', NULL, 'Failed login attempt for username: gjayborlasa@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:31:53'),
(9, NULL, 'system', 'LOGIN_FAILED', NULL, 'Failed login attempt for username: main.glorimojay.borlasa@cvsu.edu.ph', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:32:22'),
(10, 1, 'admin', 'LOGIN_SUCCESS', NULL, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:32:26'),
(11, 1, 'admin', 'USER_CREATED', NULL, 'Created user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:43:05'),
(12, NULL, 'system', 'LOGIN_FAILED', NULL, 'Failed login attempt for username: main.glorimojay.borlasa@cvsu.edu.ph', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:43:14'),
(13, NULL, 'Joshua', 'LOGIN_SUCCESS', NULL, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 00:43:21'),
(14, NULL, 'Joshua', 'SUDO_RULE_CREATED', 'SUDO', 'Created sudo rule: DEVELOPERS CAN RESTART SERVICES', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 05:26:49'),
(15, NULL, 'Joshua', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 06:12:19'),
(16, NULL, 'Joshua', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 06:12:30'),
(17, NULL, 'Joshua', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 06:12:36'),
(18, NULL, 'Joshua', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 06:12:37'),
(19, 1, 'system', 'LOGIN_FAILED', 'AUTH', 'Invalid password for user: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'WARNING', 0, '2026-02-11 06:12:53'),
(20, 1, 'system', 'LOGIN_FAILED', 'AUTH', 'Invalid password for user: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'WARNING', 0, '2026-02-11 06:13:13'),
(21, 1, 'admin', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'INFO', 1, '2026-02-11 06:13:21'),
(22, 1, 'system', 'LOGIN_FAILED', 'AUTH', 'Invalid password for user: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'WARNING', 0, '2026-02-13 00:51:23'),
(23, 1, 'admin', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 00:51:33'),
(24, 1, 'admin', 'HBAC_RULE_CREATED', 'HBAC', 'Created HBAC rule: DEVELOPERS CAN RESTART SERVICES', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 03:00:31'),
(25, 1, 'admin', 'HBAC_RULE_CREATED', 'HBAC', 'Created HBAC rule: DEVELOPERS CAN SHUTDOWN SERVICES', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 03:00:59'),
(26, 1, 'admin', 'USER_DELETED', 'USER', 'Deleted user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:33:13'),
(27, 1, 'admin', 'ROLE_CREATED', 'ROLE', 'Created role: MAGNANAKAW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:33:46'),
(28, 1, 'admin', 'HOST_ADDED', 'HOST', 'Added host: server01 (192.168.1.2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:34:48'),
(29, 1, 'admin', 'USER_DELETED', 'USER', 'Deleted user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:39:11'),
(30, 1, 'admin', 'USER_CREATED', 'USER', 'Created user: Joshua (UID: 1002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:39:27'),
(31, 1, 'admin', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:41:01'),
(32, NULL, 'Joshua', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:41:10'),
(33, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:41:30'),
(34, NULL, 'Joshua', 'CERTIFICATE_ISSUED', 'PKI', 'Issued certificate for: CN=JAY, OU=WEB, O=IT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:42:06'),
(35, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:53:24'),
(36, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:53:46'),
(37, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:54:59'),
(38, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 05:59:42'),
(39, NULL, 'Joshua', 'MFA_SETUP', 'MFA', 'MFA token setup for user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:05:49'),
(40, NULL, 'Joshua', 'USER_MODIFIED', 'USER', 'Modified user: admin (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:06:02'),
(41, NULL, 'Joshua', 'DNS_ZONE_ADDED', 'DNS', 'Added DNS zone: google.com (forward)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:17:35'),
(42, NULL, 'Joshua', 'DNS_RECORD_ADDED', 'DNS', 'Added DNS record: hahah AAAA 192.168.1.2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:17:52'),
(43, NULL, 'Joshua', 'USER_DELETED', 'USER', 'Deleted user: Glorimo Jay Borlasa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:18:33'),
(44, NULL, 'Joshua', 'USER_CREATED', 'USER', 'Created user: Glorimo Jay Borlasa (UID: 1003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:19:05'),
(45, NULL, 'Joshua', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:19:12'),
(46, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:19:22'),
(47, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:19:32'),
(48, 8, 'Glorimo Jay Borlasa', 'USER_CREATED', 'USER', 'Created user: Joshua (UID: 1004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:19:45'),
(49, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:24:43'),
(50, 8, 'Glorimo Jay Borlasa', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:43:31'),
(51, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:43:37'),
(52, 8, 'Glorimo Jay Borlasa', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 06:43:40'),
(53, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-13 07:25:36'),
(54, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'INFO', 1, '2026-02-16 05:11:52'),
(55, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 02:51:35'),
(56, 8, 'Glorimo Jay Borlasa', 'USER_CREATED', 'USER', 'Created user: ada (UID: 1004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:03:46'),
(57, 8, 'Glorimo Jay Borlasa', 'USER_CREATED', 'USER', 'Created user: qweqwr (UID: 1005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:04:12'),
(58, 8, 'Glorimo Jay Borlasa', 'USER_CREATED', 'USER', 'Created user: 2345tetqa (UID: 1006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:04:26'),
(59, 8, 'Glorimo Jay Borlasa', 'USER_CREATED', 'USER', 'Created user: asdyfjufgu (UID: 1007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:04:40'),
(60, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: 2345tetqa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:05:01'),
(61, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: ada', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:05:01'),
(62, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: qweqwr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:05:04'),
(63, 8, 'Glorimo Jay Borlasa', 'USER_DELETED', 'USER', 'Deleted user: asdyfjufgu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 03:05:06'),
(64, 8, 'Glorimo Jay Borlasa', 'LOGOUT', 'AUTH', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 04:57:30'),
(65, 8, 'system', 'LOGIN_FAILED', 'AUTH', 'Invalid password for user: gjayborlasa@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'WARNING', 0, '2026-02-18 04:57:39'),
(66, 1, 'admin', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 04:57:53'),
(67, 1, 'admin', 'ROLE_CREATED', 'ROLE', 'Created role: asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:04:33'),
(68, 1, 'admin', 'ROLE_DELETED', 'ROLE', 'Deleted role: asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'WARNING', 1, '2026-02-18 05:09:42'),
(69, 1, 'admin', 'ROLE_DELETED', 'ROLE', 'Deleted role: MAGNANAKAW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'WARNING', 1, '2026-02-18 05:18:26'),
(70, 1, 'admin', 'ROLE_MODIFIED', 'ROLE', 'Modified role: developers', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:18:39'),
(71, 1, 'admin', 'USER_MODIFIED', 'USER', 'Modified user: Glorimo Jay Borlasaaaa (ID: 8)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:18:56'),
(72, 1, 'admin', 'USER_MODIFIED', 'USER', 'Modified user: Glorimo Jay Borlasa (ID: 8)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:19:05'),
(73, 1, 'admin', 'USER_MODIFIED', 'USER', 'Modified user: Glorimo Jay Borlasa (ID: 8)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:21:45'),
(74, 1, 'admin', 'USER_MODIFIED', 'USER', 'Modified user: Glorimo Jay Borlasaaaaa (ID: 8)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:21:50'),
(75, 1, 'admin', 'USER_MODIFIED', 'USER', 'Modified user: Glorimo Jay Borlasa (ID: 8)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:21:58'),
(76, 1, 'admin', 'ROLE_MODIFIED', 'ROLE', 'Modified role: User Managerrrrrr', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:22:12'),
(77, 1, 'admin', 'ROLE_MODIFIED', 'ROLE', 'Modified role: User Manager', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 05:22:18'),
(78, 1, 'admin', 'ROLE_DELETED', 'ROLE', 'Deleted role: developers', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'WARNING', 1, '2026-02-18 05:22:27'),
(79, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-18 08:23:26'),
(80, 8, 'Glorimo Jay Borlasa', 'LOGIN_SUCCESS', 'AUTH', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 'INFO', 1, '2026-02-26 01:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(64) NOT NULL,
  `subject_dn` text NOT NULL,
  `issuer_dn` text NOT NULL,
  `certificate` text NOT NULL,
  `private_key` text DEFAULT NULL,
  `not_before` datetime NOT NULL,
  `not_after` datetime NOT NULL,
  `cert_type` enum('user','host','service','ca') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `revoked` tinyint(1) DEFAULT 0,
  `revoked_at` datetime DEFAULT NULL,
  `revocation_reason` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `serial_number`, `subject_dn`, `issuer_dn`, `certificate`, `private_key`, `not_before`, `not_after`, `cert_type`, `user_id`, `host_id`, `revoked`, `revoked_at`, `revocation_reason`, `created_at`) VALUES
(4, '698eb9ae02260', 'CN=JAY, OU=WEB, O=IT', 'CN=Freeaypa CA, O=Freeaypa Identity Management', '-----BEGIN SIMULATED CERTIFICATE-----\nSubject: CN=JAY, OU=WEB, O=IT\nIssuer: CN=Freeaypa CA\nSerial: 698eb9ae02260\nValid: 2026-02-13 06:42:06 to 2027-02-13 06:42:06\n-----END SIMULATED CERTIFICATE-----\n', NULL, '2026-02-13 06:42:06', '2027-02-13 06:42:06', 'host', NULL, 1, 0, NULL, NULL, '2026-02-13 13:42:06');

-- --------------------------------------------------------

--
-- Table structure for table `crl_entries`
--

CREATE TABLE `crl_entries` (
  `id` int(11) NOT NULL,
  `certificate_id` int(11) DEFAULT NULL,
  `serial_number` varchar(64) NOT NULL,
  `revoked_at` datetime NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `next_update` datetime NOT NULL,
  `crl_number` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dns_records`
--

CREATE TABLE `dns_records` (
  `id` int(11) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `record_name` varchar(255) DEFAULT NULL,
  `record_type` enum('A','AAAA','CNAME','MX','TXT','NS','PTR','SRV') NOT NULL,
  `record_data` text DEFAULT NULL,
  `ttl` int(11) DEFAULT 3600,
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dns_records`
--

INSERT INTO `dns_records` (`id`, `zone_id`, `record_name`, `record_type`, `record_data`, `ttl`, `priority`, `created_at`, `updated_at`) VALUES
(3, 1, 'hahah', 'AAAA', '192.168.1.2', 3600, 0, '2026-02-13 06:17:52', '2026-02-13 06:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `dns_zones`
--

CREATE TABLE `dns_zones` (
  `id` int(11) NOT NULL,
  `zone_name` varchar(255) NOT NULL,
  `zone_type` enum('forward','reverse') DEFAULT 'forward',
  `serial` int(11) DEFAULT 1,
  `refresh` int(11) DEFAULT 3600,
  `retry` int(11) DEFAULT 600,
  `expire` int(11) DEFAULT 86400,
  `ttl` int(11) DEFAULT 3600,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dns_zones`
--

INSERT INTO `dns_zones` (`id`, `zone_name`, `zone_type`, `serial`, `refresh`, `retry`, `expire`, `ttl`, `enabled`, `created_at`) VALUES
(1, 'google.com', 'forward', 1, 3600, 600, 86400, 3600, 1, '2026-02-13 06:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `email_otps`
--

CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `consumed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_otps`
--

INSERT INTO `email_otps` (`id`, `user_id`, `code_hash`, `expires_at`, `consumed`, `created_at`) VALUES
(2, 8, '$2y$10$EIe/sCibBsgCSibuvGyiHeVhuS8Lv.TWxHN23wXaOc4t5ic089uNq', '2026-02-12 23:34:03', 0, '2026-02-13 06:19:03');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `groupname` varchar(50) NOT NULL,
  `gid` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system_group` tinyint(1) DEFAULT 0,
  `is_external` tinyint(1) DEFAULT 0,
  `external_source` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `groupname`, `gid`, `description`, `is_system_group`, `is_external`, `external_source`, `created_at`) VALUES
(1, 'admins', 1000, 'System administrators', 1, 0, NULL, '2026-02-11 01:46:11'),
(2, 'users', 1001, 'Regular users', 1, 0, NULL, '2026-02-11 01:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `group_nesting`
--

CREATE TABLE `group_nesting` (
  `parent_group_id` int(11) NOT NULL,
  `child_group_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hbac_host_rules`
--

CREATE TABLE `hbac_host_rules` (
  `hbac_rule_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hbac_rules`
--

CREATE TABLE `hbac_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `allow_deny` enum('allow','deny') DEFAULT 'allow',
  `time_constraint` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hbac_rules`
--

INSERT INTO `hbac_rules` (`id`, `name`, `description`, `enabled`, `allow_deny`, `time_constraint`, `created_at`) VALUES
(1, 'DEVELOPERS CAN RESTART SERVICES', 'wla', 1, 'allow', NULL, '2026-02-13 03:00:31'),
(2, 'DEVELOPERS CAN SHUTDOWN SERVICES', 'HMM', 1, 'deny', NULL, '2026-02-13 03:00:59');

-- --------------------------------------------------------

--
-- Table structure for table `hbac_user_rules`
--

CREATE TABLE `hbac_user_rules` (
  `hbac_rule_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE `hosts` (
  `id` int(11) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `fqdn` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `os_type` varchar(50) DEFAULT NULL,
  `os_version` varchar(50) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `host_key` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hosts`
--

INSERT INTO `hosts` (`id`, `hostname`, `fqdn`, `ip_address`, `mac_address`, `description`, `os_type`, `os_version`, `enrolled_at`, `last_seen_at`, `is_active`, `host_key`, `created_at`, `updated_at`) VALUES
(1, 'server01', 'https://gjayborlasaportfolio.ct.ws/?i=2', '192.168.1.2', NULL, 'ahghgag', NULL, NULL, '2026-02-13 05:34:48', NULL, 1, NULL, '2026-02-13 05:34:48', '2026-02-13 05:34:48');

-- --------------------------------------------------------

--
-- Table structure for table `host_groups`
--

CREATE TABLE `host_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `host_group_members`
--

CREATE TABLE `host_group_members` (
  `host_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `id_ranges`
--

CREATE TABLE `id_ranges` (
  `id` int(11) NOT NULL,
  `range_name` varchar(100) NOT NULL,
  `range_type` enum('uid','gid') NOT NULL,
  `start_id` int(11) NOT NULL,
  `end_id` int(11) NOT NULL,
  `next_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_tokens`
--

CREATE TABLE `otp_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token_serial` varchar(50) NOT NULL,
  `token_type` enum('totp','hotp','yubikey') DEFAULT 'totp',
  `secret_key` varchar(64) DEFAULT NULL,
  `counter` int(11) DEFAULT 0,
  `enabled` tinyint(1) DEFAULT 1,
  `last_used` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_policies`
--

CREATE TABLE `password_policies` (
  `id` int(11) NOT NULL,
  `policy_name` varchar(100) NOT NULL,
  `min_length` int(11) DEFAULT 8,
  `require_uppercase` tinyint(1) DEFAULT 1,
  `require_lowercase` tinyint(1) DEFAULT 1,
  `require_numbers` tinyint(1) DEFAULT 1,
  `require_special` tinyint(1) DEFAULT 1,
  `max_age_days` int(11) DEFAULT 90,
  `min_age_days` int(11) DEFAULT 1,
  `history_size` int(11) DEFAULT 5,
  `lockout_attempts` int(11) DEFAULT 5,
  `lockout_duration_minutes` int(11) DEFAULT 30,
  `grace_period_days` int(11) DEFAULT 7,
  `apply_to_all` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `policy_assignments`
--

CREATE TABLE `policy_assignments` (
  `policy_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privileges`
--

CREATE TABLE `privileges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Administrator', 'Full system access', '2026-02-11 00:22:11'),
(2, 'User Manager', 'Can manage users', '2026-02-11 00:22:11'),
(3, 'Auditor', 'Can view audit logs', '2026-02-11 00:22:11'),
(4, 'Regular User', 'Basic access', '2026-02-11 00:22:11');

-- --------------------------------------------------------

--
-- Table structure for table `role_privileges`
--

CREATE TABLE `role_privileges` (
  `role_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `principal_name` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ssh_keys`
--

CREATE TABLE `ssh_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `key_fingerprint` varchar(64) NOT NULL,
  `public_key` text NOT NULL,
  `key_type` varchar(50) DEFAULT NULL,
  `key_comment` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sudo_host_rules`
--

CREATE TABLE `sudo_host_rules` (
  `sudo_rule_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sudo_rules`
--

CREATE TABLE `sudo_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `runas_user` varchar(50) DEFAULT NULL,
  `runas_group` varchar(50) DEFAULT NULL,
  `commands` text DEFAULT NULL,
  `nopasswd` tinyint(1) DEFAULT 0,
  `time_constraint` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sudo_rules`
--

INSERT INTO `sudo_rules` (`id`, `name`, `description`, `enabled`, `runas_user`, `runas_group`, `commands`, `nopasswd`, `time_constraint`, `created_at`) VALUES
(1, 'DEVELOPERS CAN RESTART SERVICES', 'HAHAHAHHAHA', 1, 'root', NULL, '/bin', 0, NULL, '2026-02-11 05:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `sudo_user_rules`
--

CREATE TABLE `sudo_user_rules` (
  `sudo_rule_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trust_relationships`
--

CREATE TABLE `trust_relationships` (
  `id` int(11) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `domain_sid` varchar(100) DEFAULT NULL,
  `trust_type` enum('ad','ipa','external') NOT NULL,
  `trust_direction` enum('inbound','outbound','bidirectional') DEFAULT 'bidirectional',
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT 1000,
  `home_directory` varchar(255) DEFAULT '/home/%s',
  `login_shell` varchar(50) DEFAULT '/bin/bash',
  `gecos` text DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `password_expires_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `mfa_secret` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `uid`, `gid`, `home_directory`, `login_shell`, `gecos`, `password_changed_at`, `password_expires_at`, `last_login_at`, `failed_login_attempts`, `locked_until`, `mfa_secret`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', '$2y$10$.owgyIWrg9uXV12yq4WBQOUjFHC20ksGaMC8s5cTfIyqlDKUWj0eS', 'admin@freeaypa.local', 1000, 1000, '/home/admin', '/bin/bash', NULL, NULL, NULL, '2026-02-17 21:57:53', 0, NULL, NULL, '2026-02-11 00:22:11', '2026-02-18 04:57:53', 0),
(8, 'Glorimo Jay Borlasa', '$2y$10$X0dOS1zQZPV4A5.0rMcCyOnYKaYm7O7uDRnvKZgX3otI4FzbQSeZK', 'gjayborlasa@gmail.com', 1003, 1000, '/home/Glorimo Jay Borlasa', '/bin/bash', 'glorimo jay borlasa', NULL, '2026-05-13 23:19:03', '2026-02-25 18:49:49', 0, NULL, NULL, '2026-02-13 06:19:03', '2026-02-26 01:49:49', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`user_id`, `group_id`, `is_primary`, `joined_at`) VALUES
(8, 2, 1, '2026-02-13 06:19:03');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, '2026-02-11 00:22:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_cert_type` (`cert_type`),
  ADD KEY `idx_expiry` (`not_after`),
  ADD KEY `idx_revoked` (`revoked`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `host_id` (`host_id`);

--
-- Indexes for table `crl_entries`
--
ALTER TABLE `crl_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `certificate_id` (`certificate_id`);

--
-- Indexes for table `dns_records`
--
ALTER TABLE `dns_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_zone` (`zone_id`),
  ADD KEY `idx_name_type` (`record_name`,`record_type`);

--
-- Indexes for table `dns_zones`
--
ALTER TABLE `dns_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_name` (`zone_name`);

--
-- Indexes for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupname` (`groupname`),
  ADD UNIQUE KEY `gid` (`gid`),
  ADD KEY `idx_gid` (`gid`);

--
-- Indexes for table `group_nesting`
--
ALTER TABLE `group_nesting`
  ADD PRIMARY KEY (`parent_group_id`,`child_group_id`),
  ADD KEY `child_group_id` (`child_group_id`);

--
-- Indexes for table `hbac_host_rules`
--
ALTER TABLE `hbac_host_rules`
  ADD PRIMARY KEY (`hbac_rule_id`,`host_id`),
  ADD KEY `host_id` (`host_id`);

--
-- Indexes for table `hbac_rules`
--
ALTER TABLE `hbac_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hbac_user_rules`
--
ALTER TABLE `hbac_user_rules`
  ADD PRIMARY KEY (`hbac_rule_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hostname` (`hostname`),
  ADD UNIQUE KEY `fqdn` (`fqdn`);

--
-- Indexes for table `host_groups`
--
ALTER TABLE `host_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `host_group_members`
--
ALTER TABLE `host_group_members`
  ADD PRIMARY KEY (`host_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `id_ranges`
--
ALTER TABLE `id_ranges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `range_name` (`range_name`),
  ADD KEY `idx_type` (`range_type`);

--
-- Indexes for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_serial` (`token_serial`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_policies`
--
ALTER TABLE `password_policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `policy_name` (`policy_name`);

--
-- Indexes for table `policy_assignments`
--
ALTER TABLE `policy_assignments`
  ADD PRIMARY KEY (`policy_id`,`user_id`,`group_id`,`role_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `privileges`
--
ALTER TABLE `privileges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_privileges`
--
ALTER TABLE `role_privileges`
  ADD PRIMARY KEY (`role_id`,`privilege_id`),
  ADD KEY `privilege_id` (`privilege_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `ssh_keys`
--
ALTER TABLE `ssh_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_fingerprint` (`key_fingerprint`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sudo_host_rules`
--
ALTER TABLE `sudo_host_rules`
  ADD PRIMARY KEY (`sudo_rule_id`,`host_id`),
  ADD KEY `host_id` (`host_id`);

--
-- Indexes for table `sudo_rules`
--
ALTER TABLE `sudo_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sudo_user_rules`
--
ALTER TABLE `sudo_user_rules`
  ADD PRIMARY KEY (`sudo_rule_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trust_relationships`
--
ALTER TABLE `trust_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_name` (`domain_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `crl_entries`
--
ALTER TABLE `crl_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dns_records`
--
ALTER TABLE `dns_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dns_zones`
--
ALTER TABLE `dns_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hbac_rules`
--
ALTER TABLE `hbac_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `host_groups`
--
ALTER TABLE `host_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_ranges`
--
ALTER TABLE `id_ranges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_policies`
--
ALTER TABLE `password_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `privileges`
--
ALTER TABLE `privileges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ssh_keys`
--
ALTER TABLE `ssh_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sudo_rules`
--
ALTER TABLE `sudo_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trust_relationships`
--
ALTER TABLE `trust_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `crl_entries`
--
ALTER TABLE `crl_entries`
  ADD CONSTRAINT `crl_entries_ibfk_1` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dns_records`
--
ALTER TABLE `dns_records`
  ADD CONSTRAINT `dns_records_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `dns_zones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD CONSTRAINT `email_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_nesting`
--
ALTER TABLE `group_nesting`
  ADD CONSTRAINT `group_nesting_ibfk_1` FOREIGN KEY (`parent_group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_nesting_ibfk_2` FOREIGN KEY (`child_group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hbac_host_rules`
--
ALTER TABLE `hbac_host_rules`
  ADD CONSTRAINT `hbac_host_rules_ibfk_1` FOREIGN KEY (`hbac_rule_id`) REFERENCES `hbac_rules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hbac_host_rules_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hbac_user_rules`
--
ALTER TABLE `hbac_user_rules`
  ADD CONSTRAINT `hbac_user_rules_ibfk_1` FOREIGN KEY (`hbac_rule_id`) REFERENCES `hbac_rules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hbac_user_rules_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `host_group_members`
--
ALTER TABLE `host_group_members`
  ADD CONSTRAINT `host_group_members_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `host_group_members_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `host_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD CONSTRAINT `otp_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `policy_assignments`
--
ALTER TABLE `policy_assignments`
  ADD CONSTRAINT `policy_assignments_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `password_policies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policy_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policy_assignments_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policy_assignments_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_privileges`
--
ALTER TABLE `role_privileges`
  ADD CONSTRAINT `role_privileges_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_privileges_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ssh_keys`
--
ALTER TABLE `ssh_keys`
  ADD CONSTRAINT `ssh_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sudo_host_rules`
--
ALTER TABLE `sudo_host_rules`
  ADD CONSTRAINT `sudo_host_rules_ibfk_1` FOREIGN KEY (`sudo_rule_id`) REFERENCES `sudo_rules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sudo_host_rules_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sudo_user_rules`
--
ALTER TABLE `sudo_user_rules`
  ADD CONSTRAINT `sudo_user_rules_ibfk_1` FOREIGN KEY (`sudo_rule_id`) REFERENCES `sudo_rules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sudo_user_rules_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
