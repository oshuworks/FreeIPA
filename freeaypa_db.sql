-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 02:10 AM
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
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin', 'SYSTEM_INIT', 'Database initialized', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:22:12'),
(2, 1, 'admin', 'LOGIN_SUCCESS', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:22:43'),
(3, 1, 'admin', 'ROLE_CREATED', 'Created role: developer', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:23:34'),
(4, 1, 'admin', 'USER_CREATED', 'Created user: Glorimo Jay Borlasa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:24:49'),
(5, 1, 'admin', 'USER_CREATED', 'Created user: Yvette Cortez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:28:56'),
(6, 1, 'admin', 'USER_DELETED', 'Deleted user: Yvette Cortez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:29:08'),
(7, NULL, 'system', 'LOGIN_FAILED', 'Failed login attempt for username: gjayborlasa@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:31:46'),
(8, NULL, 'system', 'LOGIN_FAILED', 'Failed login attempt for username: gjayborlasa@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:31:53'),
(9, NULL, 'system', 'LOGIN_FAILED', 'Failed login attempt for username: main.glorimojay.borlasa@cvsu.edu.ph', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:32:22'),
(10, 1, 'admin', 'LOGIN_SUCCESS', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:32:26'),
(11, 1, 'admin', 'USER_CREATED', 'Created user: Joshua', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:43:05'),
(12, NULL, 'system', 'LOGIN_FAILED', 'Failed login attempt for username: main.glorimojay.borlasa@cvsu.edu.ph', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:43:14'),
(13, 5, 'Joshua', 'LOGIN_SUCCESS', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2026-02-11 00:43:21');

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
(4, 'Regular User', 'Basic access', '2026-02-11 00:22:11'),
(5, 'developer', 'hhahahaha', '2026-02-11 00:23:34');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `is_active`) VALUES
(1, 'admin', '$2y$10$.owgyIWrg9uXV12yq4WBQOUjFHC20ksGaMC8s5cTfIyqlDKUWj0eS', 'admin@freeaypa.local', '2026-02-11 00:22:11', 1),
(2, 'Glorimo Jay Borlasa', '$2y$10$vnpAdVxYvxY1A7a.yZT8oeFFNLwFxKpUVmM17xkDQ0AjBjYChRZ2u', 'gjayborlasa@gmail.com', '2026-02-11 00:24:49', 1),
(5, 'Joshua', '$2y$10$SNvqqdyYraOQIm4zY12kv.fhGlOH401HY1ORg9WS1p7qcSz/OnEPG', 'main.glorimojay.borlasa@cvsu.edu.ph', '2026-02-11 00:43:05', 1);

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
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crl_entries`
--
ALTER TABLE `crl_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dns_records`
--
ALTER TABLE `dns_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dns_zones`
--
ALTER TABLE `dns_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hbac_rules`
--
ALTER TABLE `hbac_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trust_relationships`
--
ALTER TABLE `trust_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
