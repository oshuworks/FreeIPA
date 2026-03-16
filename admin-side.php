<?php
// ============================================
// TUBERO MANAGEMENT SYSTEM - ADMIN PANEL
// ============================================

session_start();

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'tubero_system';

// Create connection without database
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $DB_NAME";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($DB_NAME);
    
    // Create tables
    createTables($conn);
} else {
    die("Error creating database: " . $conn->error);
}

// Function to create all necessary tables
function createTables($conn) {
    // Plumbers table
    $sql = "CREATE TABLE IF NOT EXISTS plumbers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        role VARCHAR(50) NOT NULL,
        service_area TEXT NOT NULL,
        service_lat DECIMAL(10,8),
        service_lng DECIMAL(11,8),
        service_radius_km DECIMAL(5,2),
        hourly_rate DECIMAL(10,2) NOT NULL,
        travel_fee_per_km DECIMAL(5,2) NOT NULL,
        availability TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Service requests table
    $sql = "CREATE TABLE IF NOT EXISTS service_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        customer_contact VARCHAR(20) NOT NULL,
        customer_address TEXT NOT NULL,
        location_area VARCHAR(100) NOT NULL,
        problem_description TEXT NOT NULL,
        distance_km DECIMAL(5,2),
        assigned_plumber_id INT,
        status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        estimated_cost DECIMAL(10,2),
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completion_date DATETIME,
        FOREIGN KEY (assigned_plumber_id) REFERENCES plumbers(id) ON DELETE SET NULL
    )";
    $conn->query($sql);
    
    // Assignments history table
    $sql = "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        plumber_id INT NOT NULL,
        assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20),
        notes TEXT,
        FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (plumber_id) REFERENCES plumbers(id) ON DELETE CASCADE
    )";
    $conn->query($sql);
    
    // Admin users table
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Check if default admin exists
    $checkAdmin = $conn->query("SELECT * FROM admin_users WHERE username = 'admin'");
    if ($checkAdmin->num_rows == 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin_users (username, password) VALUES ('admin', '$defaultPassword')");
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_plumber':
                addPlumber($conn, $_POST);
                break;
            case 'assign_plumber':
                assignPlumber($conn, $_POST);
                break;
            case 'update_request_status':
                updateRequestStatus($conn, $_POST);
                break;
            case 'login':
                loginAdmin($conn, $_POST);
                break;
            case 'logout':
                logoutAdmin();
                break;
        }
    }
}

// Function to add plumber
function addPlumber($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO plumbers (full_name, contact_number, email, role, service_area, service_lat, service_lng, service_radius_km, hourly_rate, travel_fee_per_km) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdddd", $data['full_name'], $data['contact_number'], $data['email'], $data['role'], $data['service_area'], $data['service_lat'], $data['service_lng'], $data['service_radius_km'], $data['hourly_rate'], $data['travel_fee_per_km']);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Plumber added successfully!";
    } else {
        $_SESSION['error'] = "Error adding plumber: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=plumbers");
    exit();
}

// Function to assign plumber
function assignPlumber($conn, $data) {
    // Calculate estimated cost
    $plumber = $conn->query("SELECT * FROM plumbers WHERE id = " . $data['plumber_id'])->fetch_assoc();
    $travelCost = $data['distance_km'] * $plumber['travel_fee_per_km'];
    $estimatedHours = 2; // Default estimated hours, can be adjusted based on problem
    $laborCost = $estimatedHours * $plumber['hourly_rate'];
    $totalCost = $travelCost + $laborCost;
    
    $stmt = $conn->prepare("UPDATE service_requests SET assigned_plumber_id = ?, status = 'assigned', distance_km = ?, estimated_cost = ? WHERE id = ?");
    $stmt->bind_param("iddi", $data['plumber_id'], $data['distance_km'], $totalCost, $data['request_id']);
    
    if ($stmt->execute()) {
        // Log assignment
        $logStmt = $conn->prepare("INSERT INTO assignments (request_id, plumber_id, status) VALUES (?, ?, 'assigned')");
        $logStmt->bind_param("ii", $data['request_id'], $data['plumber_id']);
        $logStmt->execute();
        
        $_SESSION['message'] = "Plumber assigned successfully! Estimated cost: ₱" . number_format($totalCost, 2);
    } else {
        $_SESSION['error'] = "Error assigning plumber: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to update request status
function updateRequestStatus($conn, $data) {
    $stmt = $conn->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $data['status'], $data['request_id']);
    
    if ($stmt->execute()) {
        if ($data['status'] == 'completed') {
            $conn->query("UPDATE service_requests SET completion_date = NOW() WHERE id = " . $data['request_id']);
        }
        $_SESSION['message'] = "Request status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating status: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to login admin
function loginAdmin($conn, $data) {
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($data['password'], $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['message'] = "Login successful!";
        } else {
            $_SESSION['error'] = "Invalid password!";
        }
    } else {
        $_SESSION['error'] = "Invalid username!";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to logout admin
function logoutAdmin() {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Handle AJAX requests
if ($page == 'get_plumber' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $plumber = $conn->query("SELECT * FROM plumbers WHERE id = $id")->fetch_assoc();
    if ($plumber) {
        header('Content-Type: application/json');
        echo json_encode($plumber);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tubero Management System - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Login Page */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .login-box .logo {
            text-align: center;
            font-size: 48px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        /* Navigation */
        .navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            background: #667eea;
            color: white;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background: #ffc107; color: #000; }
        .badge-assigned { background: #17a2b8; color: #fff; }
        .badge-completed { background: #28a745; color: #fff; }
        .badge-cancelled { background: #dc3545; color: #fff; }
        
        /* Forms */
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* Messages */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        /* Map */
        #serviceMap {
            height: 400px;
            width: 100%;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .map-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .map-controls label {
            font-weight: 500;
        }
        
        .map-controls input {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 80px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']): ?>
            <!-- Login Page -->
            <div class="login-container">
                <div class="login-box">
                    <div class="logo">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h2>Tubero Management System</h2>
                    <h3 style="text-align: center; margin-bottom: 20px;">Admin Login</h3>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <button type="submit" class="btn" style="width: 100%;">Login</button>
                    </form>
                    <p style="text-align: center; margin-top: 20px; color: #666;">
                        Default: admin / admin123
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Panel -->
            <div class="navbar">
                <div class="nav-links">
                    <a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="?page=plumbers" class="<?php echo $page == 'plumbers' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Plumbers
                    </a>
                    <a href="?page=requests" class="<?php echo $page == 'requests' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-list"></i> Service Requests
                    </a>
                    <a href="?page=add_plumber" class="<?php echo $page == 'add_plumber' ? 'active' : ''; ?>">
                        <i class="fas fa-user-plus"></i> Add Plumber
                    </a>
                </div>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php
            // Dashboard Page
            if ($page == 'dashboard') {
                $totalPlumbers = $conn->query("SELECT COUNT(*) as count FROM plumbers")->fetch_assoc()['count'];
                $availablePlumbers = $conn->query("SELECT COUNT(*) as count FROM plumbers WHERE availability = 1")->fetch_assoc()['count'];
                $pendingRequests = $conn->query("SELECT COUNT(*) as count FROM service_requests WHERE status = 'pending'")->fetch_assoc()['count'];
                $totalRequests = $conn->query("SELECT COUNT(*) as count FROM service_requests")->fetch_assoc()['count'];
                ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-tools"></i>
                        <h3>Total Plumbers</h3>
                        <div class="number"><?php echo $totalPlumbers; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3>Available Plumbers</h3>
                        <div class="number"><?php echo $availablePlumbers; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3>Pending Requests</h3>
                        <div class="number"><?php echo $pendingRequests; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Total Requests</h3>
                        <div class="number"><?php echo $totalRequests; ?></div>
                    </div>
                </div>
                
                <div class="table-container">
                    <h3 style="margin-bottom: 20px;">Recent Service Requests</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Problem</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentRequests = $conn->query("SELECT sr.*, p.full_name as plumber_name FROM service_requests sr LEFT JOIN plumbers p ON sr.assigned_plumber_id = p.id ORDER BY sr.request_date DESC LIMIT 10");
                            while ($request = $recentRequests->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['location_area']); ?></td>
                                <td><?php echo substr(htmlspecialchars($request['problem_description']), 0, 50) . '...'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $request['status']; ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                <td>
                                    <?php if ($request['status'] == 'pending'): ?>
                                        <button class="btn" onclick="openAssignModal(<?php echo $request['id']; ?>, '<?php echo addslashes($request['customer_name']); ?>', '<?php echo addslashes($request['location_area']); ?>')">
                                            <i class="fas fa-user-plus"></i> Assign
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            
            // Plumbers List Page
            elseif ($page == 'plumbers') {
                $plumbers = $conn->query("SELECT * FROM plumbers ORDER BY created_at DESC");
                ?>
                <div class="table-container">
                    <h3 style="margin-bottom: 20px;">Plumbers List</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Service Area</th>
                                <th>Hourly Rate</th>
                                <th>Travel Fee/km</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($plumber = $plumbers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plumber['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($plumber['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($plumber['role']); ?></td>
                                <td><?php echo htmlspecialchars($plumber['service_area']); ?></td>
                                <td>₱<?php echo number_format($plumber['hourly_rate'], 2); ?></td>
                                <td>₱<?php echo number_format($plumber['travel_fee_per_km'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $plumber['availability'] ? 'assigned' : 'cancelled'; ?>">
                                        <?php echo $plumber['availability'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn" onclick="viewPlumberDetails(<?php echo $plumber['id']; ?>, '<?php echo addslashes($plumber['full_name']); ?>', <?php echo $plumber['service_lat'] ?: 'null'; ?>, <?php echo $plumber['service_lng'] ?: 'null'; ?>, <?php echo $plumber['service_radius_km'] ?: 'null'; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            
            // Service Requests Page
            elseif ($page == 'requests') {
                $requests = $conn->query("SELECT sr.*, p.full_name as plumber_name FROM service_requests sr LEFT JOIN plumbers p ON sr.assigned_plumber_id = p.id ORDER BY sr.request_date DESC");
                ?>
                <div class="table-container">
                    <h3 style="margin-bottom: 20px;">Service Requests</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Assigned Plumber</th>
                                <th>Status</th>
                                <th>Est. Cost</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($request = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['customer_contact']); ?></td>
                                <td><?php echo htmlspecialchars($request['location_area']); ?></td>
                                <td><?php echo $request['plumber_name'] ? htmlspecialchars($request['plumber_name']) : 'Not Assigned'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $request['status']; ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td>₱<?php echo $request['estimated_cost'] ? number_format($request['estimated_cost'], 2) : 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_request_status">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $request['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="assigned" <?php echo $request['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                            <option value="in_progress" <?php echo $request['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $request['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $request['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            
            // Add Plumber Page
            elseif ($page == 'add_plumber') {
                ?>
                <div class="form-container">
                    <h2 style="text-align: center; margin-bottom: 30px;">Add New Plumber</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_plumber">
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Contact Number *</label>
                            <input type="text" name="contact_number" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label>Role/Specialization *</label>
                            <select name="role" required>
                                <option value="">Select Role</option>
                                <option value="General Plumber">General Plumber</option>
                                <option value="Pipe Fitter">Pipe Fitter</option>
                                <option value="Drainage Specialist">Drainage Specialist</option>
                                <option value="Water Heater Specialist">Water Heater Specialist</option>
                                <option value="Emergency Plumber">Emergency Plumber</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Service Area/Location Coverage *</label>
                            <textarea name="service_area" placeholder="e.g., Metro Manila, Cavite, Laguna" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Service Area Map *</label>
                            <div class="map-controls">
                                <label>Latitude: <input type="number" step="0.000001" id="service_lat" name="service_lat" readonly></label>
                                <label>Longitude: <input type="number" step="0.000001" id="service_lng" name="service_lng" readonly></label>
                                <label>Radius (km): <input type="number" step="0.1" id="service_radius" name="service_radius_km" min="1" max="100" value="10"></label>
                            </div>
                            <div id="serviceMap"></div>
                            <small>Click on the map to set the center of the service area, then adjust the radius.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Hourly Rate (₱) *</label>
                            <input type="number" step="0.01" name="hourly_rate" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Travel Fee per Kilometer (₱) *</label>
                            <input type="number" step="0.01" name="travel_fee_per_km" required>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">Add Plumber</button>
                    </form>
                </div>
                <?php
            }
            ?>
            
            <!-- Assignment Modal -->
            <div id="assignModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Assign Plumber to Request</h3>
                        <span class="close-modal" onclick="closeAssignModal()">&times;</span>
                    </div>
                    <form method="POST" id="assignForm">
                        <input type="hidden" name="action" value="assign_plumber">
                        <input type="hidden" name="request_id" id="modal_request_id">
                        
                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" id="modal_customer_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Location Area</label>
                            <input type="text" id="modal_location" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Distance (km) *</label>
                            <input type="number" step="0.1" name="distance_km" id="modal_distance" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Select Plumber *</label>
                            <select name="plumber_id" required>
                                <option value="">Select Plumber</option>
                                <?php
                                $availablePlumbers = $conn->query("SELECT * FROM plumbers WHERE availability = 1 ORDER BY full_name");
                                while ($plumber = $availablePlumbers->fetch_assoc()):
                                ?>
                                <option value="<?php echo $plumber['id']; ?>">
                                    <?php echo htmlspecialchars($plumber['full_name']); ?> - 
                                    <?php echo htmlspecialchars($plumber['role']); ?> - 
                                    ₱<?php echo $plumber['hourly_rate']; ?>/hr
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">Assign Plumber</button>
                    </form>
                </div>
            </div>
            
            <!-- Plumber Details Modal -->
            <div id="plumberModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="plumberModalTitle">Plumber Details</h3>
                        <span class="close-modal" onclick="closePlumberModal()">&times;</span>
                    </div>
                    <div id="plumberDetails">
                        <div class="form-group">
                            <label>Name:</label>
                            <span id="plumber_name"></span>
                        </div>
                        <div class="form-group">
                            <label>Contact:</label>
                            <span id="plumber_contact"></span>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <span id="plumber_email"></span>
                        </div>
                        <div class="form-group">
                            <label>Role:</label>
                            <span id="plumber_role"></span>
                        </div>
                        <div class="form-group">
                            <label>Service Area:</label>
                            <span id="plumber_area"></span>
                        </div>
                        <div class="form-group">
                            <label>Service Area Map:</label>
                            <div id="plumberMap" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                function openAssignModal(requestId, customerName, location) {
                    document.getElementById('assignModal').style.display = 'flex';
                    document.getElementById('modal_request_id').value = requestId;
                    document.getElementById('modal_customer_name').value = customerName;
                    document.getElementById('modal_location').value = location;
                }
                
                function closeAssignModal() {
                    document.getElementById('assignModal').style.display = 'none';
                }
                
                function viewPlumberDetails(id, name, lat, lng, radius) {
                    document.getElementById('plumberModalTitle').innerText = 'Details for ' + name;
                    
                    // Fetch full details via AJAX or just show what we have
                    // For now, we'll need to pass more data or fetch
                    // Since we have limited data, let's modify to fetch from server
                    
                    // For simplicity, let's add data attributes or fetch
                    fetch('?page=get_plumber&id=' + id)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('plumber_name').innerText = data.full_name;
                            document.getElementById('plumber_contact').innerText = data.contact_number;
                            document.getElementById('plumber_email').innerText = data.email || 'N/A';
                            document.getElementById('plumber_role').innerText = data.role;
                            document.getElementById('plumber_area').innerText = data.service_area;
                            
                            // Initialize map
                            if (window.plumberMapInstance) {
                                window.plumberMapInstance.remove();
                            }
                            
                            var mapElement = document.getElementById('plumberMap');
                            window.plumberMapInstance = L.map(mapElement).setView([data.service_lat || 14.5995, data.service_lng || 120.9842], 10);
                            
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(window.plumberMapInstance);
                            
                            if (data.service_lat && data.service_lng && data.service_radius_km) {
                                L.circle([data.service_lat, data.service_lng], {
                                    color: 'blue',
                                    fillColor: '#3388ff',
                                    fillOpacity: 0.2,
                                    radius: data.service_radius_km * 1000
                                }).addTo(window.plumberMapInstance);
                                
                                L.marker([data.service_lat, data.service_lng]).addTo(window.plumberMapInstance);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching plumber details:', error);
                        });
                    
                    document.getElementById('plumberModal').style.display = 'flex';
                }
                
                function closePlumberModal() {
                    document.getElementById('plumberModal').style.display = 'none';
                }
                
                // Close modal when clicking outside
                window.onclick = function(event) {
                    var modal = document.getElementById('assignModal');
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
                
                // Initialize map for add plumber page
                if (document.getElementById('serviceMap')) {
                    var map = L.map('serviceMap').setView([14.5995, 120.9842], 10); // Default to Manila
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    var serviceCircle = null;
                    var serviceMarker = null;
                    
                    function updateCircle() {
                        var lat = parseFloat(document.getElementById('service_lat').value);
                        var lng = parseFloat(document.getElementById('service_lng').value);
                        var radius = parseFloat(document.getElementById('service_radius').value) * 1000; // Convert km to meters
                        
                        if (serviceCircle) {
                            map.removeLayer(serviceCircle);
                        }
                        if (serviceMarker) {
                            map.removeLayer(serviceMarker);
                        }
                        
                        if (!isNaN(lat) && !isNaN(lng) && !isNaN(radius)) {
                            serviceCircle = L.circle([lat, lng], {
                                color: 'red',
                                fillColor: '#f03',
                                fillOpacity: 0.2,
                                radius: radius
                            }).addTo(map);
                            
                            serviceMarker = L.marker([lat, lng]).addTo(map);
                        }
                    }
                    
                    map.on('click', function(e) {
                        document.getElementById('service_lat').value = e.latlng.lat.toFixed(6);
                        document.getElementById('service_lng').value = e.latlng.lng.toFixed(6);
                        updateCircle();
                    });
                    
                    document.getElementById('service_radius').addEventListener('input', updateCircle);
                    
                    // Set default values
                    document.getElementById('service_lat').value = 14.5995;
                    document.getElementById('service_lng').value = 120.9842;
                    updateCircle();
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>