<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$users = $db->getAll('users');
$current_user = null;

foreach($users as $user) {
    if($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}

$message = '';
$message_type = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    // Update basic info
    $current_user['name'] = $name;
    $current_user['email'] = $email;
    $current_user['phone'] = $phone;
    
    // Update password if provided
    if(!empty($current_password) && !empty($new_password)) {
        if(password_verify($current_password, $current_user['password'])) {
            $current_user['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $message = 'Current password is incorrect';
            $message_type = 'danger';
        }
    }
    
    // Update user in database
    foreach($users as $key => $user) {
        if($user['id'] == $_SESSION['user_id']) {
            $users[$key] = $current_user;
            break;
        }
    }
    
    // Save to CSV
    $filename = 'data/users.csv';
    if(!empty($users)) {
        $file = fopen($filename, 'w');
        fputcsv($file, array_keys($users[0]));
        foreach($users as $user) {
            fputcsv($file, array_values($user));
        }
        fclose($file);
    }
    
    // Update session
    $_SESSION['username'] = $name;
    $_SESSION['user_email'] = $email;
    
    if(empty($message)) {
        $message = 'Profile updated successfully';
        $message_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Housora Living</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent: #2c3e50;
        }

        .profile-card {
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--accent), #34495e);
            color: white;
            padding: 40px;
            border-radius: 16px 16px 0 0;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #666;
            margin: -75px auto 20px;
        }
    </style>
</head>
<body>
    <?php 
    $current_page = 'profile';
    include 'includes/header.php'; 
    ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header text-center">
                        <h1>My Profile</h1>
                        <p>Manage your personal information and account settings</p>
                    </div>
                    
                    <div class="profile-pic">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo $current_user['name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo $current_user['email'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo $current_user['phone'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Member Since</label>
                                    <input type="text" class="form-control" value="<?php echo date('M d, Y', strtotime($current_user['created_at'] ?? 'now')); ?>" readonly>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <h5>Change Password</h5>
                                    <p class="text-muted">Leave blank to keep current password</p>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password">
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>