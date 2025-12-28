<?php
session_start();
require_once 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['property_id'])) {
    header('Location: catalog.php');
    exit();
}

$property_id = $_GET['property_id'];
$properties = $db->getAll('properties');
$property = null;

foreach($properties as $p) {
    if($p['id'] == $property_id) {
        $property = $p;
        break;
    }
}

if(!$property) {
    header('Location: catalog.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if(empty($full_name) || empty($email) || empty($phone) || empty($payment_method)) {
        $error = 'Please fill in all required fields';
    } else {
        // Prepare transaction data
        $transactionData = [
            'id' => uniqid('trans_'),
            'property_id' => $property_id,
            'property_title' => $property['title'],
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['username'],
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'total_amount' => $property['price'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert transaction
        if($db->insert('transactions', $transactionData)) {
            $success = 'Purchase request submitted successfully! Our agent will contact you within 24 hours.';
            
            // Update property status
            $property['status'] = 'pending';
            // $db->update('properties', $property_id, $property); // Uncomment when update is implemented
        } else {
            $error = 'Failed to submit purchase request. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Form | Housora Living</title>
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
        }

        .purchase-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .property-summary {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--medium-gray);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .purchase-form {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--medium-gray);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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

        .price-tag {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i>Housora Living
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 pt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold">Complete Your Purchase</h1>
                <p class="text-muted">Fill in your details to purchase this property</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Property Summary -->
            <div class="col-lg-4">
                <div class="property-summary">
                    <h4 class="mb-4">Property Details</h4>
                    
                    <?php if(isset($property['image'])): ?>
                    <img src="<?php echo $property['image']; ?>" class="img-fluid rounded mb-4" alt="<?php echo $property['title']; ?>">
                    <?php endif; ?>
                    
                    <h5><?php echo $property['title']; ?></h5>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo $property['location']; ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="price-tag"><?php echo $property['price']; ?></div>
                        <div class="text-muted">
                            <span class="me-3"><i class="fas fa-bed me-1"></i> <?php echo $property['bedrooms']; ?> Bd</span>
                            <span><i class="fas fa-bath me-1"></i> <?php echo $property['bathrooms']; ?> Ba</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Property Features</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Type: <?php echo ucfirst($property['type']); ?></li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Area: <?php echo $property['area'] ?? 'N/A'; ?> sqft</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Built: <?php echo $property['year_built'] ?? 'N/A'; ?></li>
                        <li><i class="fas fa-check text-success me-2"></i> Status: <?php echo ucfirst($property['status'] ?? 'available'); ?></li>
                    </ul>
                </div>
            </div>

            <!-- Purchase Form -->
            <div class="col-lg-8">
                <div class="purchase-form">
                    <h4 class="mb-4">Buyer Information</h4>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo $_SESSION['username']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['user_email']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select class="form-control" name="payment_method" required>
                                        <option value="">Select Method</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="mortgage">Mortgage</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Full Address</label>
                                    <textarea class="form-control" name="address" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-4">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea class="form-control" name="notes" rows="4" placeholder="Any special requirements or questions..."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a> and confirm that all information provided is accurate.
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Purchase Request</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Important Information -->
                <div class="alert alert-info mt-4">
                    <h5><i class="fas fa-info-circle me-2"></i> Important Information</h5>
                    <ul class="mb-0">
                        <li>Our agent will contact you within 24 hours to discuss next steps</li>
                        <li>A 10% deposit is required to secure the property</li>
                        <li>All transactions are protected by our secure payment system</li>
                        <li>Property viewing can be arranged upon request</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-4">Housora Living</h4>
                    <p>Secure your dream home with confidence.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 Housora Living. All rights reserved.</p>
                    <p class="mb-0">
                        <a href="#" class="text-white me-3">Privacy Policy</a>
                        <a href="#" class="text-white">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>