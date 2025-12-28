<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/spreadsheet.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all users
global $db;
$users = $db->getAll('users');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Housora Admin</title>
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
            <a href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="properties.php">
                <i class="fas fa-building me-2"></i> Properties
            </a>
            <a href="users.php" class="active">
                <i class="fas fa-users me-2"></i> Users
            </a>
            <a href="transactions.php">
                <i class="fas fa-exchange-alt me-2"></i> Transactions
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
        <h1 class="mb-4">Manage Users</h1>
        
        <!-- User Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3><?php echo count($users); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3><?php echo count(array_filter($users, fn($user) => ($user['role'] ?? 'user') == 'admin')); ?></h3>
                        <p>Admins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3><?php echo count(array_filter($users, fn($user) => ($user['role'] ?? 'user') == 'user')); ?></h3>
                        <p>Regular Users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Users</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-1"></i> Add New User
                </button>
            </div>
            <div class="card-body">
                <?php if(empty($users)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No users found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                <tr>
                                    <td><?php echo substr($user['id'] ?? 'N/A', 0, 8); ?>...</td>
                                    <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $role = $user['role'] ?? 'user';
                                        $badge_class = ($role == 'admin') ? 'warning' : 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst($role); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple form validation for add user
        document.querySelector('#addUserModal .btn-primary').addEventListener('click', function() {
            const form = document.getElementById('addUserForm');
            const name = form.name.value.trim();
            const email = form.email.value.trim();
            
            if(!name || !email) {
                alert('Please fill in all required fields');
                return;
            }
            
            if(!validateEmail(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            alert('User added successfully (demo)');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
            form.reset();
        });
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>
</html>