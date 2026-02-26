<?php
// Freeaypa - Enhanced Identity Management System
// Single PHP file with MySQL, Bootstrap CDN

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'freeaypa_db');

// Email Configuration
define('MAIL_FROM_ADDRESS', 'noreply@freeaypa.local');
define('MAIL_FROM_NAME', 'Freeaypa');

session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Enhanced Database Initialization
function initDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);
    
    // Check if we need to upgrade existing tables
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $table_exists = $result->num_rows > 0;
    
    if ($table_exists) {
        // Check if users table has POSIX columns
        $columns_result = $conn->query("SHOW COLUMNS FROM users");
        $has_posix = false;
        while ($col = $columns_result->fetch_assoc()) {
            if ($col['Field'] == 'uid') {
                $has_posix = true;
                break;
            }
        }
        
        if (!$has_posix) {
            // Upgrade existing tables to add POSIX columns
            upgradeToPOSIX($conn);
        }
    } else {
        // Create enhanced users table with POSIX attributes and lifecycle
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            uid INT UNIQUE,
            gid INT DEFAULT 1000,
            home_directory VARCHAR(255) DEFAULT '/home/%s',
            login_shell VARCHAR(50) DEFAULT '/bin/bash',
            gecos TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            password_changed_at TIMESTAMP NULL,
            password_expires_at TIMESTAMP NULL,
            last_login_at TIMESTAMP NULL,
            failed_login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            mfa_secret VARCHAR(32),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_uid (uid),
            INDEX idx_gid (gid),
            INDEX idx_active (is_active)
        )");
        
        // Enhanced groups table with POSIX attributes
        $conn->query("CREATE TABLE IF NOT EXISTS groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            groupname VARCHAR(50) UNIQUE NOT NULL,
            gid INT UNIQUE NOT NULL,
            description TEXT,
            is_system_group BOOLEAN DEFAULT FALSE,
            is_external BOOLEAN DEFAULT FALSE,
            external_source VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_gid (gid)
        )");
        
        // User-group membership with nesting support
        $conn->query("CREATE TABLE IF NOT EXISTS user_groups (
            user_id INT,
            group_id INT,
            is_primary BOOLEAN DEFAULT FALSE,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, group_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
            INDEX idx_primary (is_primary)
        )");
        
        // Group nesting (parent-child relationships)
        $conn->query("CREATE TABLE IF NOT EXISTS group_nesting (
            parent_group_id INT,
            child_group_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (parent_group_id, child_group_id),
            FOREIGN KEY (parent_group_id) REFERENCES groups(id) ON DELETE CASCADE,
            FOREIGN KEY (child_group_id) REFERENCES groups(id) ON DELETE CASCADE
        )");
        
        // Enhanced roles table with RBAC
        $conn->query("CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            is_system_role BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Privileges for RBAC
        $conn->query("CREATE TABLE IF NOT EXISTS privileges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            category VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Role-privilege mapping
        $conn->query("CREATE TABLE IF NOT EXISTS role_privileges (
            role_id INT,
            privilege_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (role_id, privilege_id),
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (privilege_id) REFERENCES privileges(id) ON DELETE CASCADE
        )");
        
        // User roles
        $conn->query("CREATE TABLE IF NOT EXISTS user_roles (
            user_id INT,
            role_id INT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            assigned_by INT,
            expires_at TIMESTAMP NULL,
            PRIMARY KEY (user_id, role_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
        )");
        
        // Host management
        $conn->query("CREATE TABLE IF NOT EXISTS hosts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hostname VARCHAR(255) UNIQUE NOT NULL,
            fqdn VARCHAR(255) UNIQUE NOT NULL,
            ip_address VARCHAR(45),
            mac_address VARCHAR(17),
            description TEXT,
            os_type VARCHAR(50),
            os_version VARCHAR(50),
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_seen_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            host_key TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Host groups
        $conn->query("CREATE TABLE IF NOT EXISTS host_groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Host-group membership
        $conn->query("CREATE TABLE IF NOT EXISTS host_group_members (
            host_id INT,
            group_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (host_id, group_id),
            FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE CASCADE,
            FOREIGN KEY (group_id) REFERENCES host_groups(id) ON DELETE CASCADE
        )");
        
        // Host-based access control (HBAC)
        $conn->query("CREATE TABLE IF NOT EXISTS hbac_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            enabled BOOLEAN DEFAULT TRUE,
            allow_deny ENUM('allow', 'deny') DEFAULT 'allow',
            time_constraint TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // HBAC user rules
        $conn->query("CREATE TABLE IF NOT EXISTS hbac_user_rules (
            hbac_rule_id INT,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (hbac_rule_id, user_id),
            FOREIGN KEY (hbac_rule_id) REFERENCES hbac_rules(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // HBAC host rules
        $conn->query("CREATE TABLE IF NOT EXISTS hbac_host_rules (
            hbac_rule_id INT,
            host_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (hbac_rule_id, host_id),
            FOREIGN KEY (hbac_rule_id) REFERENCES hbac_rules(id) ON DELETE CASCADE,
            FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE CASCADE
        )");
        
        // Sudo rules management
        $conn->query("CREATE TABLE IF NOT EXISTS sudo_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            enabled BOOLEAN DEFAULT TRUE,
            runas_user VARCHAR(50),
            runas_group VARCHAR(50),
            commands TEXT,
            nopasswd BOOLEAN DEFAULT FALSE,
            time_constraint TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Sudo user rules
        $conn->query("CREATE TABLE IF NOT EXISTS sudo_user_rules (
            sudo_rule_id INT,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (sudo_rule_id, user_id),
            FOREIGN KEY (sudo_rule_id) REFERENCES sudo_rules(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Sudo host rules
        $conn->query("CREATE TABLE IF NOT EXISTS sudo_host_rules (
            sudo_rule_id INT,
            host_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (sudo_rule_id, host_id),
            FOREIGN KEY (sudo_rule_id) REFERENCES sudo_rules(id) ON DELETE CASCADE,
            FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE CASCADE
        )");
        
        // Certificate Authority (Basic PKI)
        $conn->query("CREATE TABLE IF NOT EXISTS certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serial_number VARCHAR(64) UNIQUE NOT NULL,
            subject_dn TEXT NOT NULL,
            issuer_dn TEXT NOT NULL,
            certificate TEXT NOT NULL,
            private_key TEXT,
            not_before TIMESTAMP NOT NULL,
            not_after TIMESTAMP NOT NULL,
            cert_type ENUM('user', 'host', 'service', 'ca') NOT NULL,
            user_id INT,
            host_id INT,
            revoked BOOLEAN DEFAULT FALSE,
            revoked_at TIMESTAMP NULL,
            revocation_reason VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cert_type (cert_type),
            INDEX idx_expiry (not_after),
            INDEX idx_revoked (revoked),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE SET NULL
        )");
        
        // Certificate Revocation List
        $conn->query("CREATE TABLE IF NOT EXISTS crl_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            certificate_id INT,
            serial_number VARCHAR(64) UNIQUE NOT NULL,
            revoked_at TIMESTAMP NOT NULL,
            reason VARCHAR(100),
            next_update TIMESTAMP NOT NULL,
            crl_number INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
        )");
        
        // DNS zones and records
        $conn->query("CREATE TABLE IF NOT EXISTS dns_zones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            zone_name VARCHAR(255) UNIQUE NOT NULL,
            zone_type ENUM('forward', 'reverse') DEFAULT 'forward',
            serial INT DEFAULT 1,
            refresh INT DEFAULT 3600,
            retry INT DEFAULT 600,
            expire INT DEFAULT 86400,
            ttl INT DEFAULT 3600,
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $conn->query("CREATE TABLE IF NOT EXISTS dns_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            zone_id INT,
            record_name VARCHAR(255),
            record_type ENUM('A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'PTR', 'SRV') NOT NULL,
            record_data TEXT,
            ttl INT DEFAULT 3600,
            priority INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_zone (zone_id),
            INDEX idx_name_type (record_name, record_type),
            FOREIGN KEY (zone_id) REFERENCES dns_zones(id) ON DELETE CASCADE
        )");
        
        // Password policies
        $conn->query("CREATE TABLE IF NOT EXISTS password_policies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            policy_name VARCHAR(100) UNIQUE NOT NULL,
            min_length INT DEFAULT 8,
            require_uppercase BOOLEAN DEFAULT TRUE,
            require_lowercase BOOLEAN DEFAULT TRUE,
            require_numbers BOOLEAN DEFAULT TRUE,
            require_special BOOLEAN DEFAULT TRUE,
            max_age_days INT DEFAULT 90,
            min_age_days INT DEFAULT 1,
            history_size INT DEFAULT 5,
            lockout_attempts INT DEFAULT 5,
            lockout_duration_minutes INT DEFAULT 30,
            grace_period_days INT DEFAULT 7,
            apply_to_all BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Policy assignments
        $conn->query("CREATE TABLE IF NOT EXISTS policy_assignments (
            policy_id INT,
            user_id INT NULL,
            group_id INT NULL,
            role_id INT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (policy_id, user_id, group_id, role_id),
            FOREIGN KEY (policy_id) REFERENCES password_policies(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        )");
        
        // Enhanced audit_logs table
        $conn->query("CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            username VARCHAR(50),
            action VARCHAR(100) NOT NULL,
            module VARCHAR(50),
            details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            severity ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
            success BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action (action),
            INDEX idx_created (created_at),
            INDEX idx_severity (severity),
            INDEX idx_user (user_id),
            INDEX idx_success (success),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");
        
        // Service management
        $conn->query("CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_name VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            principal_name VARCHAR(255),
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Trust relationships (simplified)
        $conn->query("CREATE TABLE IF NOT EXISTS trust_relationships (
            id INT AUTO_INCREMENT PRIMARY KEY,
            domain_name VARCHAR(255) UNIQUE NOT NULL,
            domain_sid VARCHAR(100),
            trust_type ENUM('ad', 'ipa', 'external') NOT NULL,
            trust_direction ENUM('inbound', 'outbound', 'bidirectional') DEFAULT 'bidirectional',
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // OTP/TOTP tokens for MFA
        $conn->query("CREATE TABLE IF NOT EXISTS otp_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_serial VARCHAR(50) UNIQUE NOT NULL,
            token_type ENUM('totp', 'hotp') DEFAULT 'totp',
            secret_key VARCHAR(255) NOT NULL,
            counter BIGINT DEFAULT 0,
            enabled BOOLEAN DEFAULT TRUE,
            last_used TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        )");
        
        // SSH key management
        $conn->query("CREATE TABLE IF NOT EXISTS ssh_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            key_fingerprint VARCHAR(64) UNIQUE NOT NULL,
            public_key TEXT NOT NULL,
            key_type VARCHAR(50),
            key_comment VARCHAR(255),
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // ID range management
        $conn->query("CREATE TABLE IF NOT EXISTS id_ranges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            range_name VARCHAR(100) UNIQUE NOT NULL,
            range_type ENUM('uid', 'gid') NOT NULL,
            start_id INT NOT NULL,
            end_id INT NOT NULL,
            next_id INT NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (range_type)
        )");
        
        // Insert default data
        $result = $conn->query("SELECT id FROM users WHERE username = 'admin'");
        if ($result->num_rows == 0) {
            // Generate secure random password for admin and save it to an initial file
            $plain_admin_password = bin2hex(random_bytes(8));
            $hashed_password = password_hash($plain_admin_password, PASSWORD_DEFAULT);
            $current_time = date('Y-m-d H:i:s');
            $password_expires = date('Y-m-d H:i:s', strtotime('+90 days'));

            // Insert admin user with POSIX attributes using prepared statement
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, uid, gid, home_directory, gecos, password_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $username_val = 'admin';
            $email_val = 'admin@freeaypa.local';
            $uid_val = 1000;
            $gid_val = 1000;
            $home_val = '/home/admin';
            $gecos_val = 'System Administrator';
            $stmt->bind_param("sssissss", $username_val, $hashed_password, $email_val, $uid_val, $gid_val, $home_val, $gecos_val, $password_expires);
            $stmt->execute();

            // Save the initial admin password to a local file with restrictive permissions
            @file_put_contents(__DIR__ . '/initial_admin_password.txt', "Admin username: admin\nPassword: $plain_admin_password\n");
            @chmod(__DIR__ . '/initial_admin_password.txt', 0600);
            
            // Create default groups with POSIX GIDs
            $conn->query("INSERT IGNORE INTO groups (groupname, gid, description, is_system_group) VALUES 
                ('admins', 1000, 'System administrators', TRUE),
                ('users', 1001, 'Regular users', TRUE),
                ('developers', 1002, 'Development team', FALSE),
                ('sysops', 1003, 'System operations', FALSE),
                ('auditors', 1004, 'Audit team', FALSE)");
            
            // Create default roles with RBAC
            $conn->query("INSERT IGNORE INTO roles (name, description, is_system_role) VALUES 
                ('Super Administrator', 'Full system access with all privileges', TRUE),
                ('User Administrator', 'Can manage users and groups', TRUE),
                ('Security Administrator', 'Can manage security policies and audit', TRUE),
                ('Host Administrator', 'Can manage hosts and services', TRUE),
                ('DNS Administrator', 'Can manage DNS zones and records', TRUE),
                ('Certificate Authority', 'Can issue and manage certificates', TRUE),
                ('Auditor', 'Read-only access to audit logs', TRUE),
                ('Regular User', 'Basic user access', TRUE)");
            
            // Create default privileges
            $conn->query("INSERT IGNORE INTO privileges (name, description, category) VALUES 
                ('user_add', 'Add new users', 'User Management'),
                ('user_modify', 'Modify existing users', 'User Management'),
                ('user_delete', 'Delete users', 'User Management'),
                ('group_add', 'Add new groups', 'Group Management'),
                ('group_modify', 'Modify groups', 'Group Management'),
                ('role_assign', 'Assign roles to users', 'RBAC'),
                ('host_manage', 'Manage hosts', 'Host Management'),
                ('dns_manage', 'Manage DNS records', 'DNS Management'),
                ('cert_issue', 'Issue certificates', 'PKI'),
                ('cert_revoke', 'Revoke certificates', 'PKI'),
                ('policy_manage', 'Manage security policies', 'Security'),
                ('audit_view', 'View audit logs', 'Audit'),
                ('sudo_manage', 'Manage sudo rules', 'Access Control'),
                ('hbac_manage', 'Manage HBAC rules', 'Access Control')");
            
            // Assign privileges to Super Administrator role
            $super_admin_id = $conn->query("SELECT id FROM roles WHERE name = 'Super Administrator'")->fetch_assoc()['id'];
            $privileges = $conn->query("SELECT id FROM privileges");
            while ($priv = $privileges->fetch_assoc()) {
                $conn->query("INSERT IGNORE INTO role_privileges (role_id, privilege_id) VALUES ($super_admin_id, {$priv['id']})");
            }
            
            // Get admin user ID and assign Super Administrator role
            $admin_id = $conn->insert_id;
            $conn->query("INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES ($admin_id, $super_admin_id, $admin_id)");
            
            // Add admin to admins group
            $admin_group_id = $conn->query("SELECT id FROM groups WHERE groupname = 'admins'")->fetch_assoc()['id'];
            $conn->query("INSERT INTO user_groups (user_id, group_id, is_primary) VALUES ($admin_id, $admin_group_id, TRUE)");
            
            // Create default password policy
            $conn->query("INSERT IGNORE INTO password_policies (policy_name, min_length, require_uppercase, require_lowercase, require_numbers, require_special, max_age_days, history_size, lockout_attempts) 
                         VALUES ('Default Policy', 12, TRUE, TRUE, TRUE, TRUE, 90, 5, 5)");
            
            // Create default ID ranges
            $conn->query("INSERT IGNORE INTO id_ranges (range_name, range_type, start_id, end_id, next_id, description) VALUES 
                ('user_uids', 'uid', 5000, 1000000, 5000, 'UID range for regular users'),
                ('system_uids', 'uid', 100, 499, 100, 'UID range for system users'),
                ('group_gids', 'gid', 5000, 1000000, 5000, 'GID range for regular groups'),
                ('system_gids', 'gid', 100, 499, 100, 'GID range for system groups')");
            
            // Create default DNS zones
            $conn->query("INSERT IGNORE INTO dns_zones (zone_name, zone_type) VALUES 
                ('freeaypa.local', 'forward'),
                ('10.in-addr.arpa', 'reverse')");
            
            // Log initialization
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $username = 'system';
            $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, username, action, module, details, ip_address, user_agent, severity) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $system_init = "SYSTEM_INIT";
            $module = "SYSTEM";
            $details = "Database initialized with default configuration";
            $severity = "INFO";
            $stmt->bind_param("isssssss", $admin_id, $username, $system_init, $module, $details, $ip, $agent, $severity);
            $stmt->execute();
        } else {
            // Check if existing users have UIDs, assign if missing
            $users_without_uid = $conn->query("SELECT id, username FROM users WHERE uid IS NULL");
            $next_uid = 5000;
            $update_stmt = $conn->prepare("UPDATE users SET uid = ?, gid = 1000, home_directory = ?, login_shell = '/bin/bash' WHERE id = ?");
            while ($user = $users_without_uid->fetch_assoc()) {
                $home = '/home/' . $user['username'];
                $update_stmt->bind_param("isi", $next_uid, $home, $user['id']);
                $update_stmt->execute();
                $next_uid++;
            }
        }
    }
    
    return $conn;
}

// Upgrade existing tables to add POSIX columns
function upgradeToPOSIX($conn) {
    // Add missing columns to users table
    $conn->query("ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS uid INT UNIQUE AFTER email,
        ADD COLUMN IF NOT EXISTS gid INT DEFAULT 1000 AFTER uid,
        ADD COLUMN IF NOT EXISTS home_directory VARCHAR(255) DEFAULT '/home/%s' AFTER gid,
        ADD COLUMN IF NOT EXISTS login_shell VARCHAR(50) DEFAULT '/bin/bash' AFTER home_directory,
        ADD COLUMN IF NOT EXISTS gecos TEXT AFTER login_shell,
        ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP NULL AFTER gecos,
        ADD COLUMN IF NOT EXISTS password_expires_at TIMESTAMP NULL AFTER password_changed_at,
        ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL AFTER password_expires_at,
        ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0 AFTER last_login_at,
        ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL AFTER failed_login_attempts,
        ADD COLUMN IF NOT EXISTS mfa_secret VARCHAR(32) AFTER locked_until,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    
    // Assign UIDs to existing users using a prepared statement
    $users = $conn->query("SELECT id, username FROM users ORDER BY id");
    $uid = 1000;
    $updateStmt = $conn->prepare("UPDATE users SET uid = ?, home_directory = ? WHERE id = ?");
    while ($user = $users->fetch_assoc()) {
        $home_dir = '/home/' . $user['username'];
        $updateStmt->bind_param("isi", $uid, $home_dir, $user['id']);
        $updateStmt->execute();
        $uid++;
    }
    
    // Add missing columns to audit_logs
    $conn->query("ALTER TABLE audit_logs
        ADD COLUMN IF NOT EXISTS module VARCHAR(50) AFTER action,
        ADD COLUMN IF NOT EXISTS severity ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO' AFTER user_agent,
        ADD COLUMN IF NOT EXISTS success BOOLEAN DEFAULT TRUE AFTER severity");
    
    // Create groups table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        groupname VARCHAR(50) UNIQUE NOT NULL,
        gid INT UNIQUE NOT NULL,
        description TEXT,
        is_system_group BOOLEAN DEFAULT FALSE,
        is_external BOOLEAN DEFAULT FALSE,
        external_source VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_gid (gid)
    )");
    
    // Insert default groups
    $conn->query("INSERT IGNORE INTO groups (groupname, gid, description, is_system_group) VALUES 
        ('admins', 1000, 'System administrators', TRUE),
        ('users', 1001, 'Regular users', TRUE)");
}

// Get database connection
$conn = initDatabase();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for POST requests
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $posted_csrf)) {
        http_response_code(400);
        $_SESSION['error'] = 'Invalid request (CSRF token)';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case 'login':
                handleLogin($conn);
                break;
            case 'logout':
                handleLogout($conn);
                break;
            case 'add_user':
                handleAddUser($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#users");
                exit;
            case 'modify_user':
                handleModifyUser($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#users");
                exit;
            case 'delete_user':
                handleDeleteUser($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#users");
                exit;
            case 'add_group':
                handleAddGroup($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#groups");
                exit;
            case 'add_role':
                handleAddRole($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#roles");
                exit;
            case 'assign_role':
                handleAssignRole($conn);
                break;
            case 'add_host':
                handleAddHost($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#hosts");
                exit;
            case 'add_dns_record':
                handleAddDNSRecord($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#dns");
                exit;
            case 'add_dns_zone':
                handleAddDNSZone($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#dns");
                exit;
            case 'add_certificate':
                handleAddCertificate($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#certificates");
                exit;
            case 'add_hbac_rule':
                handleAddHBACRule($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#hbac");
                exit;
            case 'add_sudo_rule':
                handleAddSudoRule($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#sudo");
                exit;
            case 'assign_policy':
                handleAssignPolicy($conn);
                break;
            case 'mfa_setup':
                handleMFASetup($conn);
                break;
            case 'verify_otp':
                handleVerifyOTP($conn);
                break;
            case 'export_users_csv':
                handleExportUsersCSV($conn);
                break;
            case 'modify_role':
                handleModifyRole($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#roles");
                exit;
            case 'delete_role':
                handleDeleteRole($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "#roles");
                exit;
        }
    }
}

// Export users to CSV
function handleExportUsersCSV($conn) {
    // Verify user is authenticated
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Unauthorized access';
        return;
    }

    // Query all users
    $result = $conn->query("SELECT id, uid, username, email, gid, home_directory, login_shell, gecos, is_active, locked_until FROM users ORDER BY uid");

    if (!$result) {
        $_SESSION['error'] = 'Failed to fetch users for export';
        return;
    }

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_Hi') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Write CSV header
    fputcsv($output, ['ID', 'UID', 'User', 'Email', 'GID', 'Home Directory', 'Login Shell', 'GECOS', 'Active', 'Locked Until']);

    // Write user rows
    while ($user = $result->fetch_assoc()) {
        $is_active = $user['is_active'] ? 'Yes' : 'No';
        $locked_until = $user['locked_until'] && strtotime($user['locked_until']) > time() ? $user['locked_until'] : 'N/A';
        
        fputcsv($output, [
            $user['id'],
            $user['uid'] ?? 'Not set',
            $user['username'],
            $user['email'] ?? 'Not set',
            $user['gid'] ?? '1000',
            $user['home_directory'] ?? '/home/' . $user['username'],
            $user['login_shell'] ?? '/bin/bash',
            $user['gecos'] ?? 'N/A',
            $is_active,
            $locked_until
        ]);
    }

    fclose($output);
    exit;
}

// Enhanced logging function
function logAction($conn, $action, $details, $user_id = null, $module = "SYSTEM", $severity = "INFO", $success = true) {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';
    $user_id = $user_id ?? ($_SESSION['user_id'] ?? null);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, username, action, module, details, ip_address, user_agent, severity, success) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssi", $user_id, $username, $action, $module, $details, $ip, $agent, $severity, $success);
    return $stmt->execute();
}

// Password policy validation
function validatePassword($password, $policy = null) {
    if (!$policy) {
        // Default policy
        $policy = [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special' => true
        ];
    }
    
    $errors = [];
    
    if (strlen($password) < $policy['min_length']) {
        $errors[] = "Password must be at least {$policy['min_length']} characters";
    }
    
    if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if ($policy['require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

// Get next available ID from range
function getNextId($conn, $range_type) {
    // Transactional allocation to avoid race conditions
    $next_id = null;
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("SELECT id, next_id FROM id_ranges WHERE range_type = ? AND next_id <= end_id ORDER BY id LIMIT 1 FOR UPDATE");
        $stmt->bind_param("s", $range_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $next_id = (int)$row['next_id'];
            $range_id = (int)$row['id'];

            // Update next_id using the row id to avoid race conditions
            $update = $conn->prepare("UPDATE id_ranges SET next_id = next_id + 1 WHERE id = ? AND next_id = ?");
            $update->bind_param("ii", $range_id, $next_id);
            $update->execute();

            $conn->commit();
            return $next_id;
        }
        $conn->rollback();
    } catch (Exception $e) {
        // On error, rollback and fall back
        @$conn->rollback();
    }
    
    // Fallback if no range defined
    if ($range_type == 'uid') {
        $result = $conn->query("SELECT MAX(uid) as max_uid FROM users");
        $row = $result->fetch_assoc();
        return $row['max_uid'] ? $row['max_uid'] + 1 : 5000;
    } else {
        $result = $conn->query("SELECT MAX(gid) as max_gid FROM groups");
        $row = $result->fetch_assoc();
        return $row['max_gid'] ? $row['max_gid'] + 1 : 5000;
    }
}

// Handler functions
function handleLogin($conn) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $otp_code = $_POST['otp_code'] ?? '';
    
    // Check if user is locked
    // Allow login by email or username
    $stmt = $conn->prepare("SELECT id, username, password, locked_until, failed_login_attempts FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            logAction($conn, 'LOGIN_FAILED', "Account locked for user: $username", null, "AUTH", "WARNING", false);
            $_SESSION['error'] = "Account is locked. Try again later.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if MFA is required
            $mfa_check = $conn->prepare("SELECT COUNT(*) as mfa_count FROM otp_tokens WHERE user_id = ? AND enabled = TRUE");
            $mfa_check->bind_param("i", $user['id']);
            $mfa_check->execute();
            $mfa_result = $mfa_check->get_result()->fetch_assoc();
            
            if ($mfa_result['mfa_count'] > 0) {
                // MFA required, verify OTP
                $otp_stmt = $conn->prepare("SELECT secret_key, token_type, counter FROM otp_tokens WHERE user_id = ? AND enabled = TRUE");
                $otp_stmt->bind_param("i", $user['id']);
                $otp_stmt->execute();
                $otp_result = $otp_stmt->get_result();
                
                $otp_valid = false;
                while ($token = $otp_result->fetch_assoc()) {
                        if ($token['token_type'] == 'totp') {
                            // Verify TOTP using HMAC-SHA1 (RFC6238-like)
                            if (verify_totp($token['secret_key'], $otp_code)) {
                                $otp_valid = true;
                                break;
                            }
                        }
                }
                
                if (!$otp_valid) {
                    // Increment failed attempts (prepared)
                    $inc = $conn->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?");
                    $inc->bind_param("i", $user['id']);
                    $inc->execute();
                    logAction($conn, 'LOGIN_FAILED', "Invalid OTP for user: $username", $user['id'], "AUTH", "WARNING", false);
                    $_SESSION['error'] = "Invalid authentication code";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
            
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            $_SESSION['mfa_verified'] = ($mfa_result['mfa_count'] > 0);

            // Reset failed attempts and update last login (prepared)
            $current_time = date('Y-m-d H:i:s');
            $upd = $conn->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = ? WHERE id = ?");
            $upd->bind_param("si", $current_time, $user['id']);
            $upd->execute();

            logAction($conn, 'LOGIN_SUCCESS', "User logged in", $user['id'], "AUTH", "INFO", true);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Increment failed attempts (prepared)
            $new_attempts = $user['failed_login_attempts'] + 1;
            $inc = $conn->prepare("UPDATE users SET failed_login_attempts = ? WHERE id = ?");
            $inc->bind_param("ii", $new_attempts, $user['id']);
            $inc->execute();

            // Lock account if too many failed attempts
            if ($new_attempts >= 5) {
                $lock_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $lock_stmt = $conn->prepare("UPDATE users SET locked_until = ? WHERE id = ?");
                $lock_stmt->bind_param("si", $lock_time, $user['id']);
                $lock_stmt->execute();
                logAction($conn, 'ACCOUNT_LOCKED', "Account locked due to too many failed attempts", $user['id'], "AUTH", "WARNING", false);
                $_SESSION['error'] = "Account has been locked. Try again in 30 minutes.";
            } else {
                logAction($conn, 'LOGIN_FAILED', "Invalid password for user: $username", $user['id'], "AUTH", "WARNING", false);
                $_SESSION['error'] = "Invalid username or password";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    logAction($conn, 'LOGIN_FAILED', "User not found: $username", null, "AUTH", "WARNING", false);
    $_SESSION['error'] = "User not found. Check your email/username. <br>Use <strong>admin</strong> for first login.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function handleLogout($conn) {
    if (isset($_SESSION['user_id'])) {
        logAction($conn, 'LOGOUT', "User logged out", $_SESSION['user_id'], "AUTH", "INFO", true);
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

function handleAddUser($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $username = $_POST['new_username'] ?? '';
    $password = $_POST['new_password'] ?? '';
    $email = $_POST['new_email'] ?? '';
    $gecos = $_POST['gecos'] ?? '';
    $shell = $_POST['shell'] ?? '/bin/bash';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required";
        return;
    }
    
    // Validate password against policy
    $policy_stmt = $conn->prepare("SELECT * FROM password_policies WHERE policy_name = 'Default Policy'");
    $policy_stmt->execute();
    $policy_result = $policy_stmt->get_result();
    $policy = $policy_result->fetch_assoc();
    
    if ($policy) {
        $password_errors = validatePassword($password, $policy);
        if (!empty($password_errors)) {
            $_SESSION['error'] = implode("<br>", $password_errors);
            return;
        }
    }
    
    // Wrap user creation in a transaction for consistency
    $conn->begin_transaction();
    try {
        // Get next UID
        $uid = getNextId($conn, 'uid');
        $gid = 1000; // Default GID for users group

        // Generate home directory
        $home_directory = '/home/' . $username;

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_expires = date('Y-m-d H:i:s', strtotime("+90 days"));

        $stmt = $conn->prepare("INSERT INTO users (username, password, email, uid, gid, home_directory, login_shell, gecos, password_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiissss", $username, $hashed_password, $email, $uid, $gid, $home_directory, $shell, $gecos, $password_expires);

        if (!$stmt->execute()) {
            throw new Exception('Insert user failed: ' . $stmt->error);
        }

        $user_id = $conn->insert_id;

        // Add to default users group using prepared stmt
        $users_group = $conn->prepare("SELECT id FROM groups WHERE groupname = ?");
        $groupname = 'users';
        $users_group->bind_param("s", $groupname);
        $users_group->execute();
        $ug_res = $users_group->get_result();
        if ($ug_row = $ug_res->fetch_assoc()) {
            $ug_stmt = $conn->prepare("INSERT INTO user_groups (user_id, group_id, is_primary) VALUES (?, ?, TRUE)");
            $ug_stmt->bind_param("ii", $user_id, $ug_row['id']);
            $ug_stmt->execute();
        }

        $conn->commit();
        logAction($conn, 'USER_CREATED', "Created user: $username (UID: $uid)", $_SESSION['user_id'], "USER", "INFO", true);
        $_SESSION['success'] = "User created successfully with UID: $uid";
    } catch (Exception $e) {
        $conn->rollback();
        logAction($conn, 'USER_CREATE_FAILED', "Failed to create user: $username - " . $e->getMessage(), $_SESSION['user_id'], "USER", "ERROR", false);
        $_SESSION['error'] = "Failed to create user: " . $e->getMessage();
    }
}

function handleModifyUser($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $user_id = $_POST['user_id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($user_id)) {
        $_SESSION['error'] = "User ID is required";
        return;
    }
    
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssii", $username, $email, $is_active, $user_id);
    
    if ($stmt->execute()) {
        logAction($conn, 'USER_MODIFIED', "Modified user: $username (ID: $user_id)", $_SESSION['user_id'], "USER", "INFO", true);
        $_SESSION['success'] = "User modified successfully";
    } else {
        $_SESSION['error'] = "Failed to modify user: " . $conn->error;
    }
}

function handleDeleteUser($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $user_id = $_POST['user_id'] ?? 0;
    
    if (empty($user_id)) {
        $_SESSION['error'] = "User ID is required";
        return;
    }
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account";
        return;
    }
    
    // Get username for logging (prepared)
    $get_user = $conn->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    $get_user->bind_param("i", $user_id);
    $get_user->execute();
    $user_result = $get_user->get_result();
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $username = $user['username'];
        
        // Delete user (prepared)
        $del = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del->bind_param("i", $user_id);
        $result = $del->execute();
        
        if ($result) {
            logAction($conn, 'USER_DELETED', "Deleted user: $username", $_SESSION['user_id'], "USER", "INFO", true);
            $_SESSION['success'] = "User deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete user: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "User not found";
    }
}

function handleAddGroup($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $groupname = $_POST['group_name'] ?? '';
    $description = $_POST['group_description'] ?? '';
    
    if (empty($groupname)) {
        $_SESSION['error'] = "Group name is required";
        return;
    }
    
    // Get next GID
    $gid = getNextId($conn, 'gid');
    
    $stmt = $conn->prepare("INSERT INTO groups (groupname, gid, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $groupname, $gid, $description);
    
    if ($stmt->execute()) {
        logAction($conn, 'GROUP_CREATED', "Created group: $groupname (GID: $gid)", $_SESSION['user_id'], "GROUP", "INFO", true);
        $_SESSION['success'] = "Group created successfully with GID: $gid";
    } else {
        $_SESSION['error'] = "Failed to create group: " . $conn->error;
    }
}

function handleAddRole($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $name = $_POST['role_name'] ?? '';
    $description = $_POST['role_description'] ?? '';
    
    if (empty($name)) {
        $_SESSION['error'] = "Role name is required";
        return;
    }
    
    // Check for duplicate role name
    $check_stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "A role with the name '$name' already exists";
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    
    if ($stmt->execute()) {
        logAction($conn, 'ROLE_CREATED', "Created role: $name", $_SESSION['user_id'], "ROLE", "INFO", true);
        $_SESSION['success'] = "Role created successfully";
    } else {
        $_SESSION['error'] = "Failed to create role: " . $conn->error;
    }
}

function handleModifyRole($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $role_id = $_POST['role_id'] ?? 0;
    $name = $_POST['role_name'] ?? '';
    $description = $_POST['role_description'] ?? '';
    
    if (empty($role_id) || empty($name)) {
        $_SESSION['error'] = "Role ID and name are required";
        return;
    }
    
    // Check if role exists
    $check_stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
    $check_stmt->bind_param("i", $role_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing_role = $check_result->fetch_assoc();
    
    if (!$existing_role) {
        $_SESSION['error'] = "Role not found";
        return;
    }
    
    // Check for duplicate name (if changing to a different name)
    if ($name !== $existing_role['name']) {
        $dup_stmt = $conn->prepare("SELECT id FROM roles WHERE name = ? AND id != ?");
        $dup_stmt->bind_param("si", $name, $role_id);
        $dup_stmt->execute();
        $dup_result = $dup_stmt->get_result();
        
        if ($dup_result->num_rows > 0) {
            $_SESSION['error'] = "A role with the name '$name' already exists";
            return;
        }
    }
    
    $stmt = $conn->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $role_id);
    
    if ($stmt->execute()) {
        logAction($conn, 'ROLE_MODIFIED', "Modified role: $name", $_SESSION['user_id'], "ROLE", "INFO", true);
        $_SESSION['success'] = "Role updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update role: " . $conn->error;
    }
}

function handleDeleteRole($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $role_id = $_POST['role_id'] ?? 0;
    
    if (empty($role_id)) {
        $_SESSION['error'] = "Role ID is required";
        return;
    }
    
    // Get role name for logging
    $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = $result->fetch_assoc();
    
    if (!$role) {
        $_SESSION['error'] = "Role not found";
        return;
    }
    
    // Delete role
    $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->bind_param("i", $role_id);
    
    if ($stmt->execute()) {
        logAction($conn, 'ROLE_DELETED', "Deleted role: " . $role['name'], $_SESSION['user_id'], "ROLE", "WARNING", true);
        $_SESSION['success'] = "Role deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete role: " . $conn->error;
    }
}

function handleAssignRole($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $user_id = $_POST['user_id'] ?? 0;
    $role_id = $_POST['role_id'] ?? 0;
    
    if (empty($user_id) || empty($role_id)) {
        $_SESSION['error'] = "User and Role are required";
        return;
    }
    
    $stmt = $conn->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $role_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        logAction($conn, 'ROLE_ASSIGNED', "Assigned role to user", $_SESSION['user_id'], "ROLE", "INFO", true);
        $_SESSION['success'] = "Role assigned successfully";
    } else {
        $_SESSION['error'] = "Failed to assign role: " . $conn->error;
    }
}

function handleAddHost($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $hostname = $_POST['hostname'] ?? '';
    $fqdn = $_POST['fqdn'] ?? $hostname;
    $ip_address = $_POST['ip_address'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($hostname)) {
        $_SESSION['error'] = "Hostname is required";
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO hosts (hostname, fqdn, ip_address, description, enrolled_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $hostname, $fqdn, $ip_address, $description);
    
    if ($stmt->execute()) {
        logAction($conn, 'HOST_ADDED', "Added host: $hostname ($ip_address)", $_SESSION['user_id'], "HOST", "INFO", true);
        $_SESSION['success'] = "Host added successfully";
    } else {
        $_SESSION['error'] = "Failed to add host: " . $conn->error;
    }
}

function handleAddDNSZone($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $zone_name = $_POST['zone_name'] ?? '';
    $zone_type = $_POST['zone_type'] ?? 'forward';
    
    if (empty($zone_name)) {
        $_SESSION['error'] = "Zone name is required";
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO dns_zones (zone_name, zone_type, enabled) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $zone_name, $zone_type);
    
    if ($stmt->execute()) {
        logAction($conn, 'DNS_ZONE_ADDED', "Added DNS zone: $zone_name ($zone_type)", $_SESSION['user_id'], "DNS", "INFO", true);
        $_SESSION['success'] = "DNS zone created successfully";
    } else {
        $_SESSION['error'] = "Failed to create DNS zone: " . $conn->error;
    }
}

function handleAddDNSRecord($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $zone_id = $_POST['zone_id'] ?? 0;
    $record_name = $_POST['record_name'] ?? '';
    $record_type = $_POST['record_type'] ?? 'A';
    $record_data = $_POST['record_data'] ?? '';
    // Validate zone exists
    $zone_id_val = is_numeric($zone_id) ? (int)$zone_id : 0;
    $check = $conn->prepare("SELECT id FROM dns_zones WHERE id = ? LIMIT 1");
    $check->bind_param("i", $zone_id_val);
    $check->execute();
    $zres = $check->get_result();
    if ($zres->num_rows === 0) {
        $_SESSION['error'] = "Selected DNS zone does not exist";
        return;
    }

    $stmt = $conn->prepare("INSERT INTO dns_records (zone_id, record_name, record_type, record_data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $zone_id_val, $record_name, $record_type, $record_data);

    if ($stmt->execute()) {
        logAction($conn, 'DNS_RECORD_ADDED', "Added DNS record: $record_name $record_type $record_data", $_SESSION['user_id'], "DNS", "INFO", true);
        $_SESSION['success'] = "DNS record added successfully";
    } else {
        $_SESSION['error'] = "Failed to add DNS record: " . $conn->error;
    }
}

function handleAddCertificate($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $subject_dn = $_POST['subject_dn'] ?? '';
    $cert_type = $_POST['cert_type'] ?? 'user';
    $user_id = $_POST['user_id'] ?? null;
    $host_id = $_POST['host_id'] ?? null;
    
    // Generate a simple certificate (in production, use proper PKI)
    $serial = uniqid();
    $not_before = date('Y-m-d H:i:s');
    $not_after = date('Y-m-d H:i:s', strtotime('+1 year'));
    
    // Simulated certificate data
    $certificate_data = "-----BEGIN SIMULATED CERTIFICATE-----\n";
    $certificate_data .= "Subject: $subject_dn\n";
    $certificate_data .= "Issuer: CN=Freeaypa CA\n";
    $certificate_data .= "Serial: $serial\n";
    $certificate_data .= "Valid: $not_before to $not_after\n";
    $certificate_data .= "-----END SIMULATED CERTIFICATE-----\n";
    
    $issuer_dn = "CN=Freeaypa CA, O=Freeaypa Identity Management";

    // Prepare dynamic insert to allow NULL user_id/host_id
    $user_id_val = is_numeric($user_id) && $user_id > 0 ? (int)$user_id : null;
    $host_id_val = is_numeric($host_id) && $host_id > 0 ? (int)$host_id : null;

    // Validate that referenced user/host exist; if not, reject request
    if ($user_id_val !== null) {
        $check = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $check->bind_param("i", $user_id_val);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $_SESSION['error'] = 'Selected user does not exist';
            return;
        }
    }
    if ($host_id_val !== null) {
        $check = $conn->prepare("SELECT id FROM hosts WHERE id = ? LIMIT 1");
        $check->bind_param("i", $host_id_val);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $_SESSION['error'] = 'Selected host does not exist';
            return;
        }
    }

    $fields = ['serial_number', 'subject_dn', 'issuer_dn', 'certificate', 'not_before', 'not_after', 'cert_type'];
    $placeholders = ['?', '?', '?', '?', '?', '?', '?'];
    $types = 'sssssss';
    $values = [$serial, $subject_dn, $issuer_dn, $certificate_data, $not_before, $not_after, $cert_type];

    if ($user_id_val !== null) {
        $fields[] = 'user_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = $user_id_val;
    } else {
        $fields[] = 'user_id';
        $placeholders[] = 'NULL';
    }

    if ($host_id_val !== null) {
        $fields[] = 'host_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = $host_id_val;
    } else {
        $fields[] = 'host_id';
        $placeholders[] = 'NULL';
    }

    $sql = "INSERT INTO certificates (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $_SESSION['error'] = "Failed to prepare certificate insert: " . $conn->error;
        return;
    }

    // Bind params dynamically
    if (strpos($types, 'i') !== false || strpos($types, 's') !== false) {
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($values); $i++) {
            $bind_names[] = &$values[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    
    if ($stmt->execute()) {
        logAction($conn, 'CERTIFICATE_ISSUED', "Issued certificate for: $subject_dn", $_SESSION['user_id'], "PKI", "INFO", true);
        $_SESSION['success'] = "Certificate issued successfully with serial: $serial";
    } else {
        $_SESSION['error'] = "Failed to issue certificate: " . $conn->error;
    }
}

function handleAddHBACRule($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $rule_name = $_POST['rule_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $allow_deny = $_POST['allow_deny'] ?? 'allow';
    
    $stmt = $conn->prepare("INSERT INTO hbac_rules (name, description, allow_deny) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $rule_name, $description, $allow_deny);
    
    if ($stmt->execute()) {
        logAction($conn, 'HBAC_RULE_CREATED', "Created HBAC rule: $rule_name", $_SESSION['user_id'], "HBAC", "INFO", true);
        $_SESSION['success'] = "HBAC rule created successfully";
    } else {
        $_SESSION['error'] = "Failed to create HBAC rule: " . $conn->error;
    }
}

function handleAddSudoRule($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $rule_name = $_POST['rule_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $commands = $_POST['commands'] ?? '';
    $runas_user = $_POST['runas_user'] ?? 'root';
    
    $stmt = $conn->prepare("INSERT INTO sudo_rules (name, description, commands, runas_user) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $rule_name, $description, $commands, $runas_user);
    
    if ($stmt->execute()) {
        logAction($conn, 'SUDO_RULE_CREATED', "Created sudo rule: $rule_name", $_SESSION['user_id'], "SUDO", "INFO", true);
        $_SESSION['success'] = "Sudo rule created successfully";
    } else {
        $_SESSION['error'] = "Failed to create sudo rule: " . $conn->error;
    }
}

function handleAssignPolicy($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $policy_id = $_POST['policy_id'] ?? 0;
    $target_type = $_POST['target_type'] ?? '';
    $target_id = $_POST['target_id'] ?? 0;
    
    if (empty($policy_id) || empty($target_type) || empty($target_id)) {
        $_SESSION['error'] = "Policy, target type and target ID are required";
        return;
    }
    
    switch ($target_type) {
        case 'user':
            $stmt = $conn->prepare("INSERT INTO policy_assignments (policy_id, user_id) VALUES (?, ?)");
            break;
        case 'group':
            $stmt = $conn->prepare("INSERT INTO policy_assignments (policy_id, group_id) VALUES (?, ?)");
            break;
        case 'role':
            $stmt = $conn->prepare("INSERT INTO policy_assignments (policy_id, role_id) VALUES (?, ?)");
            break;
        default:
            $_SESSION['error'] = "Invalid target type";
            return;
    }
    
    $stmt->bind_param("ii", $policy_id, $target_id);
    
    if ($stmt->execute()) {
        logAction($conn, 'POLICY_ASSIGNED', "Assigned policy to $target_type", $_SESSION['user_id'], "POLICY", "INFO", true);
        $_SESSION['success'] = "Policy assigned successfully";
    } else {
        $_SESSION['error'] = "Failed to assign policy: " . $conn->error;
    }
}

function handleMFASetup($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $user_id = $_SESSION['user_id'];
    $token_type = $_POST['token_type'] ?? 'totp';
    
    // Generate a simple secret (in production, use proper cryptographically secure random)
    $secret = bin2hex(random_bytes(16));
    
    $stmt = $conn->prepare("INSERT INTO otp_tokens (user_id, token_serial, token_type, secret_key) VALUES (?, ?, ?, ?)");
    $token_serial = 'TOKEN-' . strtoupper(substr(md5($secret), 0, 8));
    $stmt->bind_param("isss", $user_id, $token_serial, $token_type, $secret);
    
    if ($stmt->execute()) {
        logAction($conn, 'MFA_SETUP', "MFA token setup for user", $user_id, "MFA", "INFO", true);
        $_SESSION['success'] = "MFA token setup successfully. Secret: $secret";
    } else {
        $_SESSION['error'] = "Failed to setup MFA: " . $conn->error;
    }
}

function handleVerifyOTP($conn) {
    if (!isset($_SESSION['user_id'])) return;
    
    $otp_code = $_POST['otp_code'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    if (empty($otp_code)) {
        $_SESSION['error'] = "OTP code is required";
        return;
    }
    
    $stmt = $conn->prepare("SELECT secret_key, token_type, counter FROM otp_tokens WHERE user_id = ? AND enabled = TRUE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $otp_valid = false;
    while ($token = $result->fetch_assoc()) {
        if ($token['token_type'] == 'totp') {
            $expected_code = substr(md5($token['secret_key'] . floor(time() / 30)), 0, 6);
            if ($otp_code == $expected_code) {
                $otp_valid = true;
                
                // Update last used (prepared)
                $upd_otp = $conn->prepare("UPDATE otp_tokens SET last_used = NOW() WHERE user_id = ? AND secret_key = ?");
                $upd_otp->bind_param("is", $user_id, $token['secret_key']);
                $upd_otp->execute();
                break;
            }
        }
    }
    
    if ($otp_valid) {
        $_SESSION['mfa_verified'] = true;
        logAction($conn, 'MFA_VERIFIED', "MFA verification successful", $user_id, "MFA", "INFO", true);
        $_SESSION['success'] = "MFA verification successful";
    } else {
        logAction($conn, 'MFA_FAILED', "MFA verification failed", $user_id, "MFA", "WARNING", false);
        $_SESSION['error'] = "Invalid OTP code";
    }
}

// TOTP verification helper (supports hex secret)
function verify_totp($secret_hex, $code, $digits = 6, $timestep = 30, $window = 1) {
    if (empty($secret_hex) || empty($code)) return false;
    // secret stored as hex; convert to binary
    $secret = @hex2bin($secret_hex);
    if ($secret === false) return false;

    $time = floor(time() / $timestep);
    for ($i = -$window; $i <= $window; $i++) {
        $counter = pack('N*', 0) . pack('N*', $time + $i); // 64-bit big-endian
        $hash = hash_hmac('sha1', $counter, $secret, true);
        $offset = ord($hash[19]) & 0x0F;
        $truncated = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        $generated = str_pad($truncated % pow(10, $digits), $digits, '0', STR_PAD_LEFT);
        if (hash_equals($generated, $code)) return true;
    }
    return false;
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Helper functions for UI
function getUserRoles($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.name 
        FROM roles r 
        JOIN user_roles ur ON r.id = ur.role_id 
        WHERE ur.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row['name'];
    }
    return $roles;
}

function getSystemStats($conn) {
    $stats = [];
    
    // Check if tables exist before querying
    $tables = ['users', 'groups', 'hosts', 'roles', 'certificates', 'dns_records', 'audit_logs', 'hbac_rules', 'sudo_rules'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $stats[$table] = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        } else {
            $stats[$table] = 0;
        }
    }
    
    return $stats;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeIPA - Enhanced Identity Management System</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
        }
        
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .auth-header .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .auth-body {
            padding: 30px;
        }
        
        .input-group-text {
            background: var(--light-color);
            border-right: none;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .sidebar {
            background: var(--dark-color);
            color: white;
            min-height: 100vh;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.2);
            color: white;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        /* Dropdown styles */
        .dropdown-toggle::after {
            display: none;
        }
        
        .dropdown-menu {
            background: var(--dark-color);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 0.5rem 0;
            margin-left: -10px;
        }
        
        .dropdown-item {
            color: rgba(255,255,255,0.8);
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background: rgba(67, 97, 238, 0.2);
            color: white;
        }
        
        .dropdown-item i {
            width: 16px;
            margin-right: 8px;
            font-size: 0.85rem;
        }
        
        .content-section {
            display: none;
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .content-body {
            padding: 20px;
            background: #f8f9fa;
            min-height: calc(100vh - 120px);
        }
        
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color);
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            color: white;
            height: 65px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .stat-icon {
            font-size: 1.2rem;
            margin-bottom: 2px;
            opacity: 0.8;
            line-height: 1;
        }
        
        .stat-number {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 1px;
            line-height: 1;
        }
        
        .stat-card > div:last-child {
            font-size: 0.75rem;
            opacity: 0.9;
            line-height: 1;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Additional styles for new features */
        .feature-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            margin-left: 5px;
        }
        
        .posix-attributes {
            background: #f8f9fa;
            border-left: 4px solid #4361ee;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        
        .cert-badge {
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
        }
        
        .dns-badge {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        
        .policy-badge {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        
        .mfa-badge {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        
        .search-box {
            max-width: 300px;
        }
        
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <!-- Enhanced Login Page with MFA option -->
    <div class="auth-container">
        <div class="auth-card fade-in">
            <div class="auth-header">
                <div class="logo">FreeIPA</div>
                <p class="mb-0">Enterprise Identity Management System</p>
            </div>
            <div class="auth-body">
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="username" placeholder="you@gmail.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">MFA Code (if enabled)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="text" class="form-control" name="otp_code" placeholder="000000">
                            <span class="input-group-text"><small>Optional</small></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
                
                <div class="mt-4 text-center">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>First Time Setup:</strong> 
                        <br>Username: <strong>admin</strong>
                        <?php 
                        if (file_exists(__DIR__ . '/initial_admin_password.txt')) {
                            $pwd_content = @file_get_contents(__DIR__ . '/initial_admin_password.txt');
                            if ($pwd_content) {
                                echo '<br><hr style="margin: 0.5rem 0;"><code style="font-size: 0.85em; color: #dc3545;">' . htmlspecialchars($pwd_content) . '</code>';
                            }
                        } else {
                            echo '<br><span class="badge bg-warning text-dark">initial_admin_password.txt not found</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Main Application -->
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Enhanced Sidebar with more menu items -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-header">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                            <small class="text-muted">
                                <?php 
                                $roles = getUserRoles($conn, $_SESSION['user_id']);
                                echo implode(', ', array_slice($roles, 0, 2));
                                if (count($roles) > 2) echo '...';
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced System Stats -->
                <div class="p-3 border-bottom">
                    <h6 class="text-uppercase text-muted mb-3">System Stats</h6>
                    <?php $stats = getSystemStats($conn); ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-people"></i></div>
                                <div class="stat-number"><?php echo $stats['users']; ?></div>
                                <div>Users</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-diagram-3"></i></div>
                                <div class="stat-number"><?php echo $stats['groups']; ?></div>
                                <div>Groups</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="bi bi-pc-display"></i></div>
                                <div class="stat-number"><?php echo $stats['hosts']; ?></div>
                                <div>Hosts</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-award"></i></div>
                                <div class="stat-number"><?php echo $stats['certificates']; ?></div>
                                <div>Certs</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="#" class="nav-link active" onclick="showSection('dashboard')">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- Identity Management Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> Identity <i class="bi bi-chevron-down float-end"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showSection('users')">
                                    <i class="bi bi-person"></i> Users <span class="badge bg-primary">POSIX</span></a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('groups')">
                                    <i class="bi bi-diagram-3"></i> Groups <span class="badge bg-primary">Nesting</span></a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('roles')">
                                    <i class="bi bi-shield-check"></i> Roles <span class="badge bg-success">RBAC</span></a></li>
                            </ul>
                        </li>
                        
                        <!-- Infrastructure Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-globe"></i> Infrastructure <i class="bi bi-chevron-down float-end"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showSection('hosts')">
                                    <i class="bi bi-pc-display"></i> Hosts</a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('dns')">
                                    <i class="bi bi-globe"></i> DNS <span class="badge dns-badge">Zones</span></a></li>
                            </ul>
                        </li>
                        
                        <!-- Security Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-shield-lock"></i> Security <i class="bi bi-chevron-down float-end"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="showSection('certificates')">
                                    <i class="bi bi-award"></i> Certificates <span class="badge cert-badge">PKI</span></a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('hbac')">
                                    <i class="bi bi-shield-lock"></i> HBAC Rules</a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('sudo')">
                                    <i class="bi bi-terminal"></i> Sudo Rules</a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('policies')">
                                    <i class="bi bi-clipboard-check"></i> Policies <span class="badge policy-badge">Security</span></a></li>
                                <li><a class="dropdown-item" href="#" onclick="showSection('mfa')">
                                    <i class="bi bi-shield-check"></i> MFA <span class="badge mfa-badge">2FA</span></a></li>
                            </ul>
                        </li>
                        
                        <!-- Audit -->
                        <li class="nav-item">
                            <a href="#" class="nav-link" onclick="showSection('audit')">
                                <i class="bi bi-clipboard-data"></i> Audit Logs
                            </a>
                        </li>
                        
                        <li class="nav-item mt-4">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="nav-link text-start w-100" style="background: none; border: none;">
                                    <i class="bi bi-box-arrow-left"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10">
                <!-- Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show m-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Enhanced Dashboard Section -->
                <div id="dashboardSection" class="content-section">
                    <div class="content-header">
                        <h4 class="mb-0">Dashboard Overview</h4>
                        <p class="text-muted mb-0">Enterprise Identity Management System v2.0</p>
                    </div>
                    <div class="content-body">
                        <div class="welcome-card">
                            <h2>FreeIPA Enterprise IAM</h2>
                            <p class="mb-3">Complete Identity & Access Management with POSIX, RBAC, PKI, DNS, HBAC, and Sudo Policies</p>
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> LDAP</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> RBAC</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> PKI</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> DNS</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> HBAC</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> Sudo</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> MFA</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-check-circle"></i> POSIX</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3 col-6">
                                <div class="dashboard-card">
                                    <div class="card-body text-center">
                                        <i class="bi bi-people display-6 text-primary mb-2"></i>
                                        <h5><?php echo $stats['users']; ?></h5>
                                        <p class="text-muted mb-0">Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="dashboard-card">
                                    <div class="card-body text-center">
                                        <i class="bi bi-pc-display display-6 text-success mb-2"></i>
                                        <h5><?php echo $stats['hosts']; ?></h5>
                                        <p class="text-muted mb-0">Hosts</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="dashboard-card">
                                    <div class="card-body text-center">
                                        <i class="bi bi-award display-6 text-warning mb-2"></i>
                                        <h5><?php echo $stats['certificates']; ?></h5>
                                        <p class="text-muted mb-0">Certificates</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="dashboard-card">
                                    <div class="card-body text-center">
                                        <i class="bi bi-shield-check display-6 text-danger mb-2"></i>
                                        <h5><?php echo $stats['hbac_rules'] + $stats['sudo_rules']; ?></h5>
                                        <p class="text-muted mb-0">Access Rules</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="bi bi-people display-6 text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">User Management</h5>
                                                <p class="text-muted mb-2">Manage users with POSIX attributes</p>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showSection('users')">
                                                    <i class="bi bi-arrow-right"></i> Manage Users
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="bi bi-award display-6 text-success"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Certificate Authority</h5>
                                                <p class="text-muted mb-2">Issue and manage certificates</p>
                                                <button class="btn btn-sm btn-outline-success" onclick="showSection('certificates')">
                                                    <i class="bi bi-arrow-right"></i> Manage PKI
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="bi bi-shield-lock display-6 text-warning"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Access Control</h5>
                                                <p class="text-muted mb-2">HBAC and Sudo policies</p>
                                                <button class="btn btn-sm btn-outline-warning" onclick="showSection('hbac')">
                                                    <i class="bi bi-arrow-right"></i> Manage Access
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Section (Enhanced with POSIX) -->
                <div id="usersSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">User Management</h4>
                            <p class="text-muted mb-0">Manage system users with POSIX attributes (UID/GID/Home/Shell)</p>
                        </div>
                        <div>
                            <button class="btn btn-success me-2" onclick="exportUsersCSV()">
                                <i class="bi bi-download me-2"></i>Export CSV
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-plus-circle me-2"></i>Add User
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>UID</th>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>POSIX Attributes</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Server-side pagination: 5 users per page
                                        $per_page = 5;
                                        $page = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
                                        $offset = ($page - 1) * $per_page;

                                        $total_result = $conn->query("SELECT COUNT(*) as cnt FROM users");
                                        $total_row = $total_result ? $total_result->fetch_assoc() : null;
                                        $total_users = $total_row ? intval($total_row['cnt']) : 0;
                                        $total_pages = $total_users > 0 ? ceil($total_users / $per_page) : 1;

                                        $result = $conn->query("SELECT * FROM users ORDER BY uid LIMIT $per_page OFFSET $offset");

                                        while ($user = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><strong><?php echo $user['uid'] ?? 'Not set'; ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['gecos'] ?? ''); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></td>
                                            <td>
                                                <?php if ($user['uid']): ?>
                                                <div class="posix-attributes">
                                                    <small>
                                                        <strong>GID:</strong> <?php echo $user['gid'] ?? '1000'; ?><br>
                                                        <strong>Home:</strong> <?php echo htmlspecialchars($user['home_directory'] ?? '/home/' . $user['username']); ?><br>
                                                        <strong>Shell:</strong> <?php echo htmlspecialchars($user['login_shell'] ?? '/bin/bash'); ?>
                                                    </small>
                                                </div>
                                                <?php else: ?>
                                                <span class="badge bg-warning">No POSIX</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['locked_until'] && strtotime($user['locked_until']) > time()): ?>
                                                    <span class="badge bg-danger badge-pill">Locked</span>
                                                <?php elseif (!$user['is_active']): ?>
                                                    <span class="badge bg-secondary badge-pill">Inactive</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success badge-pill">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="setEditUserData(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', <?php echo $user['is_active']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <?php else: ?>
                                                <span class="badge bg-info">Current User</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>

                                <!-- Pagination controls (preserve users section via hash) -->
                                <div class="mt-2 d-flex justify-content-end">
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?user_page=<?php echo $page - 1; ?>#users">Previous</a>
                                            </li>
                                            <?php else: ?>
                                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                            <?php endif; ?>

                                            <?php
                                            $start = max(1, $page - 2);
                                            $end = min($total_pages, $page + 2);
                                            for ($p = $start; $p <= $end; $p++):
                                            ?>
                                            <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?user_page=<?php echo $p; ?>#users"><?php echo $p; ?></a>
                                            </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?user_page=<?php echo $page + 1; ?>#users">Next</a>
                                            </li>
                                            <?php else: ?>
                                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Groups Section -->
                <div id="groupsSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Group Management</h4>
                            <p class="text-muted mb-0">Manage POSIX groups with nesting support</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Group
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <?php
                            $result = $conn->query("
                                SELECT g.*, COUNT(DISTINCT ug.user_id) as user_count
                                FROM groups g
                                LEFT JOIN user_groups ug ON g.id = ug.group_id
                                GROUP BY g.id
                                ORDER BY g.gid
                            ");
                            while ($group = $result->fetch_assoc()):
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="me-3">
                                                <i class="bi bi-diagram-3 display-6 text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($group['groupname']); ?>
                                                    <span class="badge bg-light text-dark">GID: <?php echo $group['gid']; ?></span>
                                                </h5>
                                                <p class="text-muted small"><?php echo htmlspecialchars($group['description']); ?></p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-primary me-1">
                                                    <i class="bi bi-people"></i> <?php echo $group['user_count']; ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $group['is_system_group'] ? 'System' : 'Custom'; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Hosts Section -->
                <div id="hostsSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Host Management</h4>
                            <p class="text-muted mb-0">Manage enrolled hosts and their properties</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHostModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Host
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Hostname</th>
                                            <th>FQDN</th>
                                            <th>IP Address</th>
                                            <th>Status</th>
                                            <th>Last Seen</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("SELECT * FROM hosts ORDER BY hostname");
                                        while ($host = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($host['hostname']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($host['description'] ?? ''); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($host['fqdn']); ?></td>
                                            <td>
                                                <code><?php echo htmlspecialchars($host['ip_address']); ?></code><br>
                                                <?php if ($host['mac_address']): ?>
                                                <small class="text-muted"><?php echo $host['mac_address']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $host['is_active'] ? 'bg-success' : 'bg-danger'; ?> badge-pill">
                                                    <?php echo $host['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo $host['last_seen_at'] ? date('M d, H:i', strtotime($host['last_seen_at'])) : 'Never'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Roles Section -->
                <div id="rolesSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Role Management</h4>
                            <p class="text-muted mb-0">Manage RBAC roles and permissions</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Role
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Role Name</th>
                                            <th>Description</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("SELECT * FROM roles ORDER BY name");
                                        while ($role = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($role['name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($role['description']); ?></td>
                                            <td>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($role['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal" onclick="setEditRoleData(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['name']); ?>', '<?php echo htmlspecialchars($role['description']); ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_role">
                                                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this role?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Certificates Section -->
                <div id="certificatesSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Certificate Management</h4>
                            <p class="text-muted mb-0">Issue and manage X.509 certificates</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificateModal">
                                <i class="bi bi-plus-circle me-2"></i>Issue Certificate
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Serial</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Valid Until</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("
                                            SELECT c.*, u.username, h.hostname
                                            FROM certificates c
                                            LEFT JOIN users u ON c.user_id = u.id
                                            LEFT JOIN hosts h ON c.host_id = h.id
                                            ORDER BY c.not_after DESC
                                            LIMIT 50
                                        ");
                                        while ($cert = $result->fetch_assoc()):
                                            $is_expired = strtotime($cert['not_after']) < time();
                                            $expires_soon = strtotime($cert['not_after']) < strtotime('+30 days');
                                        ?>
                                        <tr>
                                            <td>
                                                <code class="small"><?php echo substr($cert['serial_number'], 0, 8); ?>...</code>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars(substr($cert['subject_dn'], 0, 50)); ?>...</strong><br>
                                                <small class="text-muted">
                                                    <?php if ($cert['username']): ?>
                                                    User: <?php echo htmlspecialchars($cert['username']); ?>
                                                    <?php elseif ($cert['hostname']): ?>
                                                    Host: <?php echo htmlspecialchars($cert['hostname']); ?>
                                                    <?php else: ?>
                                                    <?php echo htmlspecialchars($cert['cert_type']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $cert['cert_type'] == 'user' ? 'bg-primary' : ''; ?>
                                                    <?php echo $cert['cert_type'] == 'host' ? 'bg-success' : ''; ?>
                                                    <?php echo $cert['cert_type'] == 'service' ? 'bg-warning' : ''; ?>
                                                    <?php echo $cert['cert_type'] == 'ca' ? 'bg-danger' : ''; ?>
                                                    badge-pill">
                                                    <?php echo strtoupper($cert['cert_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo date('M d, Y', strtotime($cert['not_after'])); ?><br>
                                                    <?php if ($is_expired): ?>
                                                    <span class="text-danger">Expired</span>
                                                    <?php elseif ($expires_soon): ?>
                                                    <span class="text-warning">Expires soon</span>
                                                    <?php else: ?>
                                                    <span class="text-success">Valid</span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($cert['revoked']): ?>
                                                <span class="badge bg-danger badge-pill">Revoked</span>
                                                <?php else: ?>
                                                <span class="badge bg-success badge-pill">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewCertificate('<?php echo $cert['serial_number']; ?>')">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <?php if (!$cert['revoked']): ?>
                                                    <button class="btn btn-outline-warning" onclick="revokeCertificate('<?php echo $cert['serial_number']; ?>')">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- DNS Section -->
                <div id="dnsSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">DNS Management</h4>
                            <p class="text-muted mb-0">Manage forward and reverse DNS zones</p>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDNSZoneModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Zone
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDNSRecordModal">
                                <i class="bi bi-link-45deg me-2"></i>Add Record
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="row mb-4">
                            <?php
                            $zones = $conn->query("SELECT * FROM dns_zones ORDER BY zone_name");
                            while ($zone = $zones->fetch_assoc()):
                                $rc_stmt = $conn->prepare("SELECT COUNT(*) as count FROM dns_records WHERE zone_id = ?");
                                $rc_stmt->bind_param("i", $zone['id']);
                                $rc_stmt->execute();
                                $record_count = $rc_stmt->get_result()->fetch_assoc()['count'];
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">
                                                <?php echo htmlspecialchars($zone['zone_name']); ?>
                                                <span class="badge <?php echo $zone['zone_type'] == 'forward' ? 'bg-primary' : 'bg-info'; ?>">
                                                    <?php echo $zone['zone_type']; ?>
                                                </span>
                                            </h5>
                                            <span class="badge bg-secondary"><?php echo $record_count; ?> records</span>
                                        </div>
                                        <div class="row small">
                                            <div class="col-6">
                                                <strong>Serial:</strong> <?php echo $zone['serial']; ?><br>
                                                <strong>TTL:</strong> <?php echo $zone['ttl']; ?>
                                            </div>
                                            <div class="col-6">
                                                <strong>Refresh:</strong> <?php echo $zone['refresh']; ?><br>
                                                <strong>Retry:</strong> <?php echo $zone['retry']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="table-container">
                            <h6 class="mb-3">DNS Records</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Zone</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Data</th>
                                            <th>TTL</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $records = $conn->query("
                                            SELECT dr.*, dz.zone_name
                                            FROM dns_records dr
                                            JOIN dns_zones dz ON dr.zone_id = dz.id
                                            ORDER BY dr.record_name, dr.record_type
                                            LIMIT 100
                                        ");
                                        while ($record = $records->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><small><?php echo htmlspecialchars($record['zone_name']); ?></small></td>
                                            <td><?php echo htmlspecialchars($record['record_name']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $record['record_type']; ?></span>
                                            </td>
                                            <td>
                                                <code class="small"><?php echo htmlspecialchars(substr($record['record_data'], 0, 50)); ?></code>
                                            </td>
                                            <td><small><?php echo $record['ttl']; ?></small></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- HBAC Rules Section -->
                <div id="hbacSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Host-Based Access Control</h4>
                            <p class="text-muted mb-0">Define who can access which hosts</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHBACRuleModal">
                                <i class="bi bi-plus-circle me-2"></i>Add HBAC Rule
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <?php
                            $rules = $conn->query("
                                SELECT h.*, 
                                       COUNT(DISTINCT hur.user_id) as user_count,
                                       COUNT(DISTINCT hhr.host_id) as host_count
                                FROM hbac_rules h
                                LEFT JOIN hbac_user_rules hur ON h.id = hur.hbac_rule_id
                                LEFT JOIN hbac_host_rules hhr ON h.id = hhr.hbac_rule_id
                                GROUP BY h.id
                                ORDER BY h.name
                            ");
                            while ($rule = $rules->fetch_assoc()):
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($rule['name']); ?>
                                                    <span class="badge <?php echo $rule['allow_deny'] == 'allow' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo strtoupper($rule['allow_deny']); ?>
                                                    </span>
                                                </h5>
                                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($rule['description']); ?></p>
                                            </div>
                                            <span class="badge <?php echo $rule['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $rule['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                        <div class="row small text-muted">
                                            <div class="col-6">
                                                <i class="bi bi-people"></i> <?php echo $rule['user_count']; ?> users
                                            </div>
                                            <div class="col-6">
                                                <i class="bi bi-pc-display"></i> <?php echo $rule['host_count']; ?> hosts
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sudo Rules Section -->
                <div id="sudoSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Sudo Rules Management</h4>
                            <p class="text-muted mb-0">Centralized sudo command control</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSudoRuleModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Sudo Rule
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rule Name</th>
                                            <th>Run As</th>
                                            <th>Commands</th>
                                            <th>No Password</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $rules = $conn->query("SELECT * FROM sudo_rules ORDER BY name");
                                        while ($rule = $rules->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($rule['name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($rule['description']); ?></small>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($rule['runas_user']); ?></code>
                                                <?php if ($rule['runas_group']): ?>
                                                <br><small class="text-muted">Group: <?php echo $rule['runas_group']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <pre class="small mb-0" style="max-height: 60px; overflow: hidden;"><?php echo htmlspecialchars($rule['commands']); ?></pre>
                                            </td>
                                            <td>
                                                <?php if ($rule['nopasswd']): ?>
                                                <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $rule['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $rule['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Policies Section -->
                <div id="policiesSection" class="content-section" style="display: none;">
                    <div class="content-header">
                        <div>
                            <h4 class="mb-0">Security Policies</h4>
                            <p class="text-muted mb-0">Password and security policy management</p>
                        </div>
                    </div>
                    <div class="content-body">
                                <div class="row">
                                    <?php
                                    $policies = $conn->query("SELECT * FROM password_policies ORDER BY policy_name");
                                    if ($policies && $policies->num_rows > 0) {
                                        while ($policy = $policies->fetch_assoc()):
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="dashboard-card">
                                            <div class="card-body">
                                                <h5 class="card-title mb-3">
                                                    <?php echo htmlspecialchars($policy['policy_name']); ?>
                                                    <?php if ($policy['apply_to_all']): ?>
                                                    <span class="badge bg-primary">Global</span>
                                                    <?php endif; ?>
                                                </h5>
                                        
                                                <div class="row small mb-3">
                                                    <div class="col-6">
                                                        <strong>Password Requirements:</strong><br>
                                                        <i class="bi bi-check-circle <?php echo $policy['require_uppercase'] ? 'text-success' : 'text-muted'; ?>"></i> Uppercase<br>
                                                        <i class="bi bi-check-circle <?php echo $policy['require_lowercase'] ? 'text-success' : 'text-muted'; ?>"></i> Lowercase<br>
                                                        <i class="bi bi-check-circle <?php echo $policy['require_numbers'] ? 'text-success' : 'text-muted'; ?>"></i> Numbers
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Settings:</strong><br>
                                                        Min Length: <?php echo $policy['min_length']; ?><br>
                                                        Max Age: <?php echo $policy['max_age_days']; ?> days<br>
                                                        History: <?php echo $policy['history_size']; ?> passwords
                                                    </div>
                                                </div>
                                        
                                                <div class="row small text-muted">
                                                    <div class="col-6">
                                                        Lockout: <?php echo $policy['lockout_attempts']; ?> attempts<br>
                                                        Duration: <?php echo $policy['lockout_duration_minutes']; ?> min
                                                    </div>
                                                    <div class="col-6">
                                                        Grace Period: <?php echo $policy['grace_period_days']; ?> days<br>
                                                        Min Age: <?php echo $policy['min_age_days']; ?> days
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        endwhile;
                                    } else {
                                        // No policies defined fallback
                                        ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No security policies found. You can create a default policy from the administration CLI or use the 'Add Policy' action (not implemented in UI).
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                    </div>
                </div>
                
                <!-- MFA Section -->
                <div id="mfaSection" class="content-section" style="display: none;">
                    <div class="content-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Multi-Factor Authentication</h4>
                            <p class="text-muted mb-0">Manage MFA tokens and settings</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMFAModal">
                                <i class="bi bi-plus-circle me-2"></i>Add MFA Token
                            </button>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <?php
                            $tokens = $conn->query("
                                SELECT o.*, u.username
                                FROM otp_tokens o
                                JOIN users u ON o.user_id = u.id
                                ORDER BY o.created_at DESC
                            ");
                            while ($token = $tokens->fetch_assoc()):
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="dashboard-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($token['token_serial']); ?></h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($token['username']); ?><br>
                                                    <i class="bi bi-shield"></i> <?php echo strtoupper($token['token_type']); ?>
                                                </p>
                                            </div>
                                            <span class="badge <?php echo $token['enabled'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $token['enabled'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            <?php if ($token['last_used']): ?>
                                            Last used: <?php echo date('M d, H:i', strtotime($token['last_used'])); ?><br>
                                            <?php endif; ?>
                                            Created: <?php echo date('M d, Y', strtotime($token['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Audit Logs Section -->
                <div id="auditSection" class="content-section" style="display: none;">
                    <div class="content-header">
                        <div>
                            <h4 class="mb-0">Audit Logs</h4>
                            <p class="text-muted mb-0">Comprehensive system activity monitoring</p>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="search-box">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search logs..." id="auditSearch">
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="filterAudit('SUCCESS')">
                                        <i class="bi bi-check-circle"></i> Success
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="filterAudit('FAILED')">
                                        <i class="bi bi-x-circle"></i> Failed
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="filterAudit('all')">
                                        <i class="bi bi-list"></i> All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="auditTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>User</th>
                                            <th>Module</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>Severity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("
                                            SELECT * FROM audit_logs 
                                            ORDER BY created_at DESC 
                                            LIMIT 100
                                        ");
                                        
                                        while ($log = $result->fetch_assoc()):
                                            $severity_class = [
                                                'INFO' => 'bg-info',
                                                'WARNING' => 'bg-warning',
                                                'ERROR' => 'bg-danger',
                                                'CRITICAL' => 'bg-dark'
                                            ][$log['severity']] ?? 'bg-secondary';
                                            
                                            $success_class = $log['success'] ? 'text-success' : 'text-danger';
                                        ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, H:i:s', strtotime($log['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo $log['module'] ?? 'SYSTEM'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $success_class; ?> badge-pill">
                                                    <?php echo $log['action']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($log['details'], 0, 80)); ?>...</small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $severity_class; ?>">
                                                    <?php echo $log['severity']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Add User Modal (Simplified - No Email Verification) -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <div>
                        <h5 class="modal-title text-white mb-0"><i class="bi bi-person-plus me-2"></i>Create New User</h5>
                        <small class="text-white-50">Add a new user with POSIX attributes</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" name="new_username" placeholder="jdoe" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">GECOS (Full Name)</label>
                                    <input type="text" class="form-control" name="gecos" placeholder="John Doe">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" name="new_email" placeholder="john.doe@example.com">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" name="new_password" placeholder="Min. 8 characters" required>
                                    </div>
                                    <small class="text-muted">Must have uppercase, lowercase, number, special char</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Login Shell</label>
                                    <select class="form-control" name="shell">
                                        <option value="/bin/bash">/bin/bash</option>
                                        <option value="/bin/sh">/bin/sh</option>
                                        <option value="/bin/zsh">/bin/zsh</option>
                                        <option value="/sbin/nologin">/sbin/nologin</option>
                                        <option value="/bin/false">/bin/false</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Primary Group</label>
                                    <select class="form-control" name="primary_group">
                                        <?php
                                        $groups = $conn->query("SELECT id, groupname FROM groups WHERE is_system_group = 1 OR groupname = 'users' ORDER BY groupname");
                                        while ($group = $groups->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $group['id']; ?>" <?php echo $group['groupname'] == 'users' ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($group['groupname']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <small class="text-muted">Default primary group for new users</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            POSIX attributes (UID, Home Directory) will be automatically assigned from the available ID ranges.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="modify_user">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-pencil me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" id="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="editIsActive" value="1">
                            <label class="form-check-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Group Modal -->
    <div class="modal fade" id="addGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_group">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-diagram-3 me-2"></i>Add New Group</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Group Name *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <input type="text" class="form-control" name="group_name" placeholder="developers" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="group_description" rows="2" placeholder="Group purpose and description"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            GID will be automatically assigned from the available range
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_role">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-shield-check me-2"></i>Add New Role</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield"></i></span>
                                <input type="text" class="form-control" name="role_name" placeholder="developer" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="role_description" rows="3" placeholder="Role description and permissions"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="modify_role">
                    <input type="hidden" name="role_id" id="editRoleId">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-shield-check me-2"></i>Edit Role</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield"></i></span>
                                <input type="text" class="form-control" name="role_name" id="editRoleName" placeholder="developer" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="role_description" id="editRoleDescription" rows="3" placeholder="Role description and permissions"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Host Modal -->
    <div class="modal fade" id="addHostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_host">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-pc-display me-2"></i>Add New Host</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Hostname *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pc"></i></span>
                                <input type="text" class="form-control" name="hostname" placeholder="server01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">FQDN (Fully Qualified Domain Name)</label>
                            <input type="text" class="form-control" name="fqdn" placeholder="server01.freeaypa.local">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">IP Address</label>
                            <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Host purpose and description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Host</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Certificate Modal -->
    <div class="modal fade" id="addCertificateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_certificate">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-award me-2"></i>Issue Certificate</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Certificate Type</label>
                            <select class="form-control" name="cert_type" id="certTypeSelect" onchange="updateCertificateForm()">
                                <option value="user">User Certificate</option>
                                <option value="host">Host Certificate</option>
                                <option value="service">Service Certificate</option>
                                <option value="ca">CA Certificate</option>
                            </select>
                        </div>
                        
                        <div id="userCertFields">
                            <div class="mb-3">
                                <label class="form-label">Select User</label>
                                <select class="form-control" name="user_id">
                                    <option value="">-- Select User --</option>
                                    <?php
                                    $users = $conn->query("SELECT id, username FROM users ORDER BY username");
                                    while ($user = $users->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div id="hostCertFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Select Host</label>
                                <select class="form-control" name="host_id">
                                    <option value="">-- Select Host --</option>
                                    <?php
                                    $hosts = $conn->query("SELECT id, hostname FROM hosts ORDER BY hostname");
                                    while ($host = $hosts->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $host['id']; ?>"><?php echo htmlspecialchars($host['hostname']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject DN *</label>
                            <input type="text" class="form-control" name="subject_dn" placeholder="CN=John Doe, OU=Users, O=Company" required>
                            <small class="text-muted">Format: CN=Common Name, OU=Organizational Unit, O=Organization</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Issue Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add DNS Record Modal -->
    <div class="modal fade" id="addDNSRecordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_dns_record">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-globe me-2"></i>Add DNS Record</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Zone *</label>
                            <select class="form-control" name="zone_id" required>
                                <option value="">-- Select a DNS Zone --</option>
                                <?php
                                $zones = $conn->query("SELECT id, zone_name, zone_type FROM dns_zones WHERE enabled = 1 ORDER BY zone_name");
                                if ($zones && $zones->num_rows > 0) {
                                    while ($zone = $zones->fetch_assoc()):
                                ?>
                                <option value="<?php echo $zone['id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?> (<?php echo $zone['zone_type']; ?>)</option>
                                <?php 
                                    endwhile;
                                } else {
                                    echo "<option disabled='disabled'>No zones available - Create one first</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted">A zone is a DNS domain (e.g., example.com) or reverse zone (e.g., 1.168.192.in-addr.arpa)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Record Name *</label>
                            <input type="text" class="form-control" name="record_name" placeholder="www" required>
                            <small class="text-muted">Leave empty for zone apex (@), or enter subdomain like 'mail', 'blog', etc.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Record Type *</label>
                            <select class="form-control" name="record_type" required>
                                <option value="A">A (IPv4 Address)</option>
                                <option value="AAAA">AAAA (IPv6 Address)</option>
                                <option value="CNAME">CNAME (Alias)</option>
                                <option value="MX">MX (Mail Server)</option>
                                <option value="TXT">TXT (Text Record)</option>
                                <option value="NS">NS (Name Server)</option>
                                <option value="PTR">PTR (Reverse DNS)</option>
                                <option value="SRV">SRV (Service)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Record Data *</label>
                            <input type="text" class="form-control" name="record_data" placeholder="192.168.1.10" required>
                            <small class="text-muted">IP address, hostname, or value depending on record type</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add DNS Zone Modal -->
    <div class="modal fade" id="addDNSZoneModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_dns_zone">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-globe me-2"></i>Add DNS Zone</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Zone Name *</label>
                            <input type="text" class="form-control" name="zone_name" placeholder="example.com" required>
                            <small class="text-muted">For forward zones: example.com | For reverse zones: 1.168.192.in-addr.arpa</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zone Type *</label>
                            <select class="form-control" name="zone_type" required>
                                <option value="forward">Forward Zone (domain names)</option>
                                <option value="reverse">Reverse Zone (IP addresses)</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Forward Zone:</strong> Maps domain names to IP addresses (e.g., www.example.com → 192.168.1.10)<br>
                            <strong>Reverse Zone:</strong> Maps IP addresses to domain names (e.g., 1.168.192.in-addr.arpa for 192.168.1.x)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Zone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add HBAC Rule Modal -->
    <div class="modal fade" id="addHBACRuleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_hbac_rule">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-shield-lock me-2"></i>Add HBAC Rule</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rule Name *</label>
                            <input type="text" class="form-control" name="rule_name" placeholder="Allow developers to access dev servers" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Rule description and purpose"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select class="form-control" name="allow_deny">
                                <option value="allow">Allow Access</option>
                                <option value="deny">Deny Access</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            After creating the rule, you can add users and hosts to it from the edit page
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Sudo Rule Modal -->
    <div class="modal fade" id="addSudoRuleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_sudo_rule">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-terminal me-2"></i>Add Sudo Rule</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rule Name *</label>
                            <input type="text" class="form-control" name="rule_name" placeholder="Developers can restart services" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Rule description and purpose"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commands * (one per line)</label>
                            <textarea class="form-control" name="commands" rows="4" placeholder="/bin/systemctl restart httpd&#10;/bin/systemctl status httpd" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Run As User</label>
                            <input type="text" class="form-control" name="runas_user" value="root">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="nopasswd" id="nopasswd">
                            <label class="form-check-label" for="nopasswd">No password required (NOPASSWD)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add MFA Modal -->
    <div class="modal fade" id="addMFAModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="mfa_setup">
                    <div class="modal-header">
                        <h5 class="modal-title text-white"><i class="bi bi-shield-check me-2"></i>Setup MFA Token</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            This will generate a new MFA token for your account. Save the secret key securely.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Token Type</label>
                            <select class="form-control" name="token_type">
                                <option value="totp">TOTP (Time-based One-Time Password)</option>
                                <option value="hotp">HOTP (HMAC-based One-Time Password)</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> After setup, you will need to use an authenticator app like Google Authenticator, Authy, or FreeOTP to generate codes.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Token</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Section Navigation
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show selected section
            const sectionEl = document.getElementById(section + 'Section');
            if (sectionEl) {
                sectionEl.style.display = 'block';
            }
            
            // Update active nav item
            document.querySelectorAll('.nav-link').forEach(el => {
                el.classList.remove('active');
            });
            
            // Find and activate the clicked nav item
            document.querySelectorAll('.nav-link').forEach(el => {
                if (el.getAttribute('onclick') && el.getAttribute('onclick').includes(section)) {
                    el.classList.add('active');
                }
            });
        }
        
        // Set data for edit user modal
        function setEditUserData(userId, username, email, isActive) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editIsActive').checked = isActive == 1;
        }

        // Set data for edit role modal
        function setEditRoleData(roleId, roleName, roleDescription) {
            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editRoleName').value = roleName;
            document.getElementById('editRoleDescription').value = roleDescription;
        }
        
        // Update certificate form based on type
        function updateCertificateForm() {
            const type = document.getElementById('certTypeSelect').value;
            document.getElementById('userCertFields').style.display = type === 'user' ? 'block' : 'none';
            document.getElementById('hostCertFields').style.display = type === 'host' ? 'block' : 'none';
        }
        
        // Filter audit logs
        function filterAudit(filter) {
            const rows = document.querySelectorAll('#auditTable tbody tr');
            rows.forEach(row => {
                const successBadge = row.cells[3].querySelector('.text-success');
                const failedBadge = row.cells[3].querySelector('.text-danger');
                
                if (filter === 'all' || 
                    (filter === 'SUCCESS' && successBadge) ||
                    (filter === 'FAILED' && failedBadge)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Search audit logs
        document.getElementById('auditSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#auditTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // View certificate details
        function viewCertificate(serial) {
            alert('Certificate details for serial: ' + serial + '\n\nIn a full implementation, this would show complete certificate details.');
        }
        
        // Revoke certificate
        function revokeCertificate(serial) {
            if (confirm('Are you sure you want to revoke certificate ' + serial + '?')) {
                alert('Certificate ' + serial + ' would be revoked in a full implementation.');
            }
        }

        // Export users to CSV
        function exportUsersCSV() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'export_users_csv';
            form.appendChild(actionInput);

            // Add CSRF token from existing form or page
            const existingForm = document.querySelector('form');
            if (existingForm) {
                const csrfInput = existingForm.querySelector('input[name="csrf_token"]');
                if (csrfInput) {
                    const csrfClone = csrfInput.cloneNode(true);
                    form.appendChild(csrfClone);
                }
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Initialize view and handle deep-links / pagination links
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);

            // If a user_page param exists, show users section
            if (params.has('user_page')) {
                showSection('users');
            } else if (window.location.hash) {
                const section = window.location.hash.replace('#', '');
                if (document.getElementById(section + 'Section')) {
                    showSection(section);
                } else {
                    showSection('dashboard');
                }
            } else {
                showSection('dashboard');
            }

            updateCertificateForm(); // Initialize certificate form
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>

<script>
// Inject CSRF token into all forms automatically
(function(){
    var token = '<?php echo $_SESSION['csrf_token']; ?>';
    document.querySelectorAll('form').forEach(function(f){
        if (!f.querySelector('input[name="csrf_token"]')){
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'csrf_token'; inp.value = token;
            f.appendChild(inp);
        }
    });
})();
</script>