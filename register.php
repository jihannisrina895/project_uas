<?php
// MATIKAN WARNING & DEPRECATED SEBELUM SEMUA HALAMAN JALAN
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// LOAD CONFIGURASI
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php'; // File yang sudah diupdate

// Redirect kalau sudah login
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// FORM PROCESSING
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if(empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email exists menggunakan helper function
        $existingUser = getUserByEmail($email);
        
        if($existingUser) {
            $error = 'Email already registered';
        } else {
            // Generate unique ID
            $id = 'user_' . time() . '_' . rand(1000, 9999);
            
            // Prepare user data
            $userData = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone,
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert user ke Google Sheets menggunakan helper function
            if(createUser($userData)) {
                $success = 'Registration successful! You can now login.';
                
                // Auto login after registration
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';
                
                // Redirect to home
                header('Location: index.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Register | Housora Living</title>
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

        .register-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--medium-gray);
        }

        .register-left {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: var(--white);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-left h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .register-right {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="register-container">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="register-left">
                                <h1>Create Account</h1>
                                <p class="mb-4">Join Housora Living today and start your journey to finding the perfect home. Access exclusive listings and premium services.</p>
                                <div class="mt-4">
                                    <p><i class="fas fa-star me-2"></i> Exclusive property listings</p>
                                    <p><i class="fas fa-bell me-2"></i> Instant notifications</p>
                                    <p><i class="fas fa-heart me-2"></i> Save favorite properties</p>
                                    <p><i class="fas fa-history me-2"></i> Track viewing history</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="register-right">
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
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address *</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password *</label>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <div class="mb-4">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary mb-3">Create Account</button>
                                    
                                    <div class="text-center">
                                        <p class="mb-0">Already have an account? 
                                            <a href="login.php" class="text-decoration-none fw-bold">Sign in</a>
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
</body>
</html>