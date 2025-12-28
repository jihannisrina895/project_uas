<?php
// LOAD CONFIGURASI
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php'; // File yang sudah diupdate

// Fungsi untuk membuat admin pertama jika belum ada
function ensureAdminExists() {
    global $db;
    
    $admin_email = 'admin@housora.com';
    
    // Cek apakah admin sudah ada
    $admin = getUserByEmail($admin_email);
    if (!$admin) {
        // Buat admin pertama
        $admin_data = [
            'id' => 'admin_' . time() . '_' . rand(1000, 9999),
            'name' => 'System Administrator',
            'email' => $admin_email,
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('users', $admin_data);
        if ($result) {
            error_log("Admin user created automatically: $admin_email");
        }
        return $admin_data;
    }
    return $admin;
}

// Panggil fungsi untuk pastikan admin ada
ensureAdminExists();

if(isset($_SESSION['user_id'])) {
    // Redirect berdasarkan role
    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if(empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Get user dari Google Sheets menggunakan helper function
        $user = getUserByEmail($email);
        
        if($user && isset($user['password'])) {
            if(password_verify($password, $user['password'])) {
                // ===== PERBAIKAN: SIMPAN SEMUA DATA SESSION =====
                $_SESSION['user_id'] = $user['id'] ?? $email;
                $_SESSION['username'] = $user['name'] ?? 'User';
                $_SESSION['user_email'] = $user['email']; // ← INI PENTING!
                $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 
                                        ($email == 'admin@housora.com' ? 'admin' : 'user');
                
                // Debug log
                error_log("Login successful - Email: $email, Role: " . $_SESSION['user_role']);
                
                // Redirect berdasarkan role
                if($_SESSION['user_role'] == 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                // Cek untuk admin default password
                if($email == 'admin@housora.com' && $password == 'Admin123!') {
                    // Jika password masih default, buat/update admin
                    $admin_data = [
                        'id' => 'admin_' . time() . '_' . rand(1000, 9999),
                        'name' => 'System Administrator',
                        'email' => $email,
                        'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
                        'role' => 'admin',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    global $db;
                    if($db->insert('users', $admin_data)) {
                        // Simpan session
                        $_SESSION['user_id'] = $admin_data['id'];
                        $_SESSION['username'] = $admin_data['name'];
                        $_SESSION['user_email'] = $admin_data['email'];
                        $_SESSION['user_role'] = 'admin';
                        
                        error_log("Admin created and logged in: $email");
                        
                        header('Location: admin/index.php');
                        exit();
                    }
                }
                $error = 'Invalid email or password';
            }
        } else {
            // Coba buat admin dengan password default
            if($email == 'admin@housora.com' && $password == 'Admin123!') {
                $admin_data = [
                    'id' => 'admin_' . time() . '_' . rand(1000, 9999),
                    'name' => 'System Administrator',
                    'email' => $email,
                    'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                global $db;
                if($db->insert('users', $admin_data)) {
                    $_SESSION['user_id'] = $admin_data['id'];
                    $_SESSION['username'] = $admin_data['name'];
                    $_SESSION['user_email'] = $admin_data['email'];
                    $_SESSION['user_role'] = 'admin';
                    
                    error_log("Admin auto-created and logged in: $email");
                    
                    header('Location: admin/index.php');
                    exit();
                } else {
                    $error = 'Failed to create admin account. Please try again.';
                }
            } else {
                $error = 'Invalid email or password';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Housora Living</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --gray: #9e9e9e;
            --dark-gray: #616161;
            --black: #212121;
            --pure-black: #000000;
            --accent: #2c3e50;
            --accent-light: #34495e;
        }

        body {
            background: var(--light-gray);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--medium-gray);
        }

        .login-left {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: var(--white);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .login-right {
            padding: 60px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 40px;
            text-align: center;
        }

        .form-control {
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }

        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px;
            border-radius: 8px;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--accent-light);
            border-color: var(--accent-light);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--gray);
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--medium-gray);
        }

        .divider span {
            padding: 0 20px;
        }
        
        /* Admin credentials info */
        .admin-info {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .admin-info h6 {
            color: #d35400;
            margin-bottom: 8px;
        }
        
        .admin-info .credentials {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f1c40f;
            margin: 10px 0;
        }
        
        .admin-info .warning {
            color: #e74c3c;
            font-size: 12px;
        }
        
        .btn-admin-quick {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border: none;
        }
        
        .btn-admin-quick:hover {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="login-left">
                                <h1>Welcome Back</h1>
                                <p class="mb-4">Sign in to access your account, manage properties, and continue your journey in finding the perfect home.</p>
                                <div class="mt-4">
                                    <p><i class="fas fa-check-circle me-2"></i> Access saved properties</p>
                                    <p><i class="fas fa-check-circle me-2"></i> Manage purchases</p>
                                    <p><i class="fas fa-check-circle me-2"></i> Explore exclusive listings</p>
                                    <p><i class="fas fa-check-circle me-2"></i> Admin panel access</p>
                                </div>
                                
                                <!-- Admin Quick Login (visible on hover) -->
                                <div class="admin-info mt-4">
                                    <h6><i class="fas fa-user-shield me-2"></i>Admin Access</h6>
                                    <div class="credentials">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Email:</strong><br>
                                                admin@housora.com
                                            </div>
                                            <div class="col-6">
                                                <strong>Password:</strong><br>
                                                Admin123!
                                            </div>
                                        </div>
                                    </div>
                                    <p class="warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Admin account is created automatically on first login
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="login-right">
                                <div class="logo">
                                    <i class="fas fa-home me-2"></i>Housora Living
                                </div>
                                
                                <?php if($error): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <?php if($success): ?>
                                    <div class="alert alert-success"><?php echo $success; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                               placeholder="Enter your email" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Enter your password" required>
                                    </div>
                                    
                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                        <a href="#" class="float-end text-decoration-none">Forgot password?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary mb-3">Sign In</button>
                                    
                                    <!-- Admin Quick Login Button -->
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-admin-quick w-100" 
                                                onclick="fillAdminCredentials()">
                                            <i class="fas fa-user-shield me-2"></i>Quick Admin Login
                                        </button>
                                    </div>
                                    
                                    <div class="divider">
                                        <span>Or sign in with</span>
                                    </div>
                                    
                                    <div class="row g-2 mb-4">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-secondary w-100">
                                                <i class="fab fa-google me-2"></i> Google
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-secondary w-100">
                                                <i class="fab fa-facebook-f me-2"></i> Facebook
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <p class="mb-0">Don't have an account? 
                                            <a href="register.php" class="text-decoration-none fw-bold">Create account</a>
                                        </p>
                                        <p class="mt-2">
                                            <a href="login.php?admin=true" class="text-decoration-none">
                                                <i class="fas fa-user-shield me-1"></i>Direct Admin Login
                                            </a>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengisi credentials admin secara otomatis
        function fillAdminCredentials() {
            document.getElementById('email').value = 'admin@housora.com';
            document.getElementById('password').value = 'Admin123!';
            
            // Show info message
            alert('Admin credentials filled. Click Sign In to login as admin.');
            
            // Auto focus on submit button
            document.querySelector('button[type="submit"]').focus();
        }
        
        // Auto-fill on page load if URL has parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('admin') === 'true') {
                fillAdminCredentials();
                // Auto submit after 1 second
                setTimeout(function() {
                    document.querySelector('form').submit();
                }, 1000);
            }
        });
    </script>
</body>
</html>