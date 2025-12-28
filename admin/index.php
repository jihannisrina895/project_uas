<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/spreadsheet.php';

// ===== DEBUG MODE =====
$debug_mode = isset($_GET['debug']);
if($debug_mode) {
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "=== DEBUG SESSION DATA ===\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Data:\n";
    print_r($_SESSION);
    echo "\n";
    
    // Cek user di database
    if(isset($_SESSION['user_email'])) {
        $user = getUserByEmail($_SESSION['user_email']);
        echo "User from Database:\n";
        print_r($user);
    } else {
        echo "No user_email in session\n";
    }
    echo "</pre>";
}
// ======================

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is admin
// Cara 1: Cek dari session role
$is_admin = false;
if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $is_admin = true;
} 
// Cara 2: Cek dari email
else if(isset($_SESSION['user_email']) && $_SESSION['user_email'] == 'admin@housora.com') {
    $is_admin = true;
    // Update session role jika belum ada
    $_SESSION['user_role'] = 'admin';
}
// Cara 3: Cek dari database
else if(isset($_SESSION['user_email'])) {
    $user = getUserByEmail($_SESSION['user_email']);
    if($user && isset($user['role']) && $user['role'] == 'admin') {
        $is_admin = true;
        $_SESSION['user_role'] = 'admin';
    }
}

// Jika bukan admin, redirect ke user index
if(!$is_admin) {
    header('Location: ../index.php');
    exit();
}

// ===== Jika sampai sini, user adalah admin =====

// Get stats
global $db;
$properties = $db->getAll('properties');
$users = $db->getAll('users');
$transactions = $db->getAll('transactions');

$total_properties = count($properties);
$total_users = count($users);
$total_transactions = count($transactions);
$pending_transactions = 0;

foreach($transactions as $transaction) {
    if(($transaction['status'] ?? 'pending') == 'pending') {
        $pending_transactions++;
    }
}

// Debug info
if($debug_mode) {
    echo "<pre style='background: #e0ffe0; padding: 10px; margin-top: 10px;'>";
    echo "=== ADMIN ACCESS GRANTED ===\n";
    echo "User: " . ($_SESSION['username'] ?? 'N/A') . "\n";
    echo "Email: " . ($_SESSION['user_email'] ?? 'N/A') . "\n";
    echo "Role: " . ($_SESSION['user_role'] ?? 'N/A') . "\n";
    echo "Total Properties: $total_properties\n";
    echo "Total Users: $total_users\n";
    echo "Total Transactions: $total_transactions\n";
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Housora Living</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent: #2c3e50;
            --accent-light: #34495e;
        }

        .sidebar {
            background: var(--accent);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: var(--accent-light);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-card.green { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .stat-card.orange { background: linear-gradient(135deg, #f46b45, #eea849); }
        .stat-card.purple { background: linear-gradient(135deg, #654ea3, #da98b4); }
        
        .admin-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffd700;
            color: #000;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4 position-relative">
            <h3><i class="fas fa-home me-2"></i> Housora Admin</h3>
            <p class="text-white-50 mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
            <div class="admin-badge">
                <i class="fas fa-crown"></i> ADMIN
            </div>
        </div>
        
        <nav>
            <a href="index.php" class="active">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building me-2"></i> Properties
            </a>
            <a href="users.php">
                <i class="fas fa-users me-2"></i> Users
            </a>
            <a href="transactions.php">
                <i class="fas fa-exchange-alt me-2"></i> Transactions
            </a>
            <a href="reports.php">
                <i class="fas fa-chart-bar me-2"></i> Reports
            </a>
            <a href="../index.php" class="mt-5">
                <i class="fas fa-globe me-2"></i> View Website
            </a>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <h1 class="mb-4">Dashboard Overview</h1>
        
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card blue">
                    <h3><?php echo $total_properties; ?></h3>
                    <p>Total Properties</p>
                    <i class="fas fa-building fa-2x float-end opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card green">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Registered Users</p>
                    <i class="fas fa-users fa-2x float-end opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card orange">
                    <h3><?php echo $total_transactions; ?></h3>
                    <p>Total Transactions</p>
                    <i class="fas fa-exchange-alt fa-2x float-end opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card purple">
                    <h3><?php echo $pending_transactions; ?></h3>
                    <p>Pending Transactions</p>
                    <i class="fas fa-clock fa-2x float-end opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="transactions.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if(empty($transactions)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No transactions yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Property</th>
                                    <th>Buyer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_transactions = array_slice(array_reverse($transactions), 0, 5);
                                foreach($recent_transactions as $transaction): 
                                    // Cari data property
                                    $property_title = 'Unknown';
                                    $amount = '$0';
                                    foreach($properties as $prop) {
                                        if(isset($prop['id']) && $prop['id'] == $transaction['property_id']) {
                                            $property_title = $prop['title'] ?? 'Unknown';
                                            $amount = '$' . number_format($prop['price'] ?? 0);
                                            break;
                                        }
                                    }
                                    
                                    // Cari data user
                                    $buyer_name = 'Unknown';
                                    foreach($users as $usr) {
                                        if(isset($usr['id']) && $usr['id'] == $transaction['user_id']) {
                                            $buyer_name = $usr['name'] ?? 'Unknown';
                                            break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?php echo substr($transaction['id'] ?? 'N/A', 0, 8); ?>...</td>
                                    <td><?php echo htmlspecialchars($property_title); ?></td>
                                    <td><?php echo htmlspecialchars($buyer_name); ?></td>
                                    <td><?php echo $amount; ?></td>
                                    <td>
                                        <?php 
                                        $status = $transaction['status'] ?? 'pending';
                                        $badge_class = 'secondary';
                                        if($status == 'completed') $badge_class = 'success';
                                        elseif($status == 'pending') $badge_class = 'warning';
                                        elseif($status == 'processing') $badge_class = 'info';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($transaction['created_at'] ?? 'now')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Properties -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Properties</h5>
                <a href="properties.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if(empty($properties)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No properties yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_properties = array_slice(array_reverse($properties), 0, 5);
                                foreach($recent_properties as $property): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($property['title'] ?? 'Untitled'); ?></td>
                                    <td><?php echo ucfirst($property['type'] ?? 'house'); ?></td>
                                    <td><?php echo htmlspecialchars($property['location'] ?? 'Unknown'); ?></td>
                                    <td>$<?php echo number_format($property['price'] ?? 0); ?></td>
                                    <td>
                                        <?php 
                                        $status = $property['status'] ?? 'available';
                                        $badge_class = 'success';
                                        if($status == 'sold') $badge_class = 'secondary';
                                        elseif($status == 'pending') $badge_class = 'warning';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="properties.php?action=edit&id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Debug Panel (only visible if debug=true) -->
        <?php if($debug_mode): ?>
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-bug me-2"></i>Debug Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Session Information:</h6>
                        <ul>
                            <li>Session ID: <?php echo session_id(); ?></li>
                            <li>User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></li>
                            <li>Username: <?php echo $_SESSION['username'] ?? 'Not set'; ?></li>
                            <li>Email: <?php echo $_SESSION['user_email'] ?? 'Not set'; ?></li>
                            <li>Role: <?php echo $_SESSION['user_role'] ?? 'Not set'; ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>System Information:</h6>
                        <ul>
                            <li>PHP Version: <?php echo phpversion(); ?></li>
                            <li>Server Time: <?php echo date('Y-m-d H:i:s'); ?></li>
                            <li>Google Script URL: <?php echo defined('GOOGLE_SCRIPT_URL') ? 'Set' : 'Not set'; ?></li>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-sm btn-secondary">Hide Debug</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh dashboard every 60 seconds
        setTimeout(function() {
            window.location.reload();
        }, 60000);
        
        // Debug shortcut
        document.addEventListener('keydown', function(e) {
            if(e.ctrlKey && e.shiftKey && e.key === 'D') {
                window.location.href = 'index.php?debug=true';
            }
        });
    </script>
</body>
</html>