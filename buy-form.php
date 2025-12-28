<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php';
require_once 'includes/functions.php'; 

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$property_id = $_GET['property_id'] ?? '';
$property = getPropertyById($property_id);
$error = '';
$success = '';

if (!$property) {
    header('Location: catalog.php');
    exit();
}

// Format harga property
$formatted_price = formatPrice($property['price'] ?? '');

// Options untuk payment method dropdown
$payment_methods = [
    'credit_card' => 'Credit Card',
    'bank_transfer' => 'Bank Transfer',
    'cash' => 'Cash',
    'paypal' => 'PayPal',
    'other' => 'Other'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'credit_card'); // Tambahkan payment_method
    $notes = trim($_POST['notes'] ?? '');
    
    // Validasi input
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } else {
        // Siapkan data sesuai dengan kolom di spreadsheet
        $purchaseData = [
            'property_id' => $property_id,
            'property_title' => $property['title'] ?? '',
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['username'] ?? '',
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'payment_method' => $payment_method, // Gunakan nilai dari dropdown
            'notes' => $notes,
            'total_amount' => $property['price'] ?? 0,
            'status' => 'pending'
        ];
        
        if (createPurchase($purchaseData)) {
            $success = 'Your purchase inquiry has been submitted successfully! We will contact you shortly.';
            // Kosongkan form
            $_POST = [];
        } else {
            $error = 'Failed to submit purchase inquiry. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Inquiry - Housora Living</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
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
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--black);
            background-color: var(--white);
            padding-top: 80px; /* Untuk navbar fixed */
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 24px;
        }

        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--accent-light);
            border-color: var(--accent-light);
            transform: translateY(-2px);
        }

        .form-control, .form-select {
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }

        .property-summary-card {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid var(--medium-gray);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .property-image-container {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .property-image-container img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--medium-gray);
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--accent);
        }

        .form-section {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--medium-gray);
            box-shadow: var(--shadow);
        }

        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent);
        }

        .required-field::after {
            content: ' *';
            color: #dc3545;
        }
        
        footer {
            background: var(--black);
            color: var(--white);
            padding: 60px 0 30px;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i>Housora Living
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="catalog.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my-purchases.php">My Purchases</a></li>
                                <?php if(($_SESSION['user_role'] ?? '') == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-crown me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline me-2" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="catalog.php">Properties</a></li>
                <li class="breadcrumb-item"><a href="property-detail.php?id=<?php echo $property_id; ?>">Property Details</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchase Inquiry</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="display-5 fw-bold">Purchase Inquiry</h1>
                <p class="lead">Complete the form below to submit your purchase inquiry for this property.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Purchase Form -->
            <div class="col-lg-8">
                <div class="form-section">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <div class="mt-3">
                                <a href="my-purchases.php" class="btn btn-primary me-2">View My Purchases</a>
                                <a href="catalog.php" class="btn btn-outline-secondary">Browse More Properties</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h3 class="section-title">Your Information</h3>
                    
                    <form method="POST" action="" id="purchaseForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label required-field">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? $_SESSION['username'] ?? ''); ?>" 
                                           required>
                                    <div class="form-text">Please enter your full legal name</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label required-field">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? $_SESSION['user_email'] ?? ''); ?>" 
                                           required>
                                    <div class="form-text">We'll send confirmation to this email</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       placeholder="+1 (555) 123-4567" required>
                            </div>
                            <div class="form-text">Include country code if international</div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label">Current Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                      placeholder="Enter your current residential address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            <div class="form-text">Used for verification purposes only</div>
                        </div>

                        <!-- Payment Method Dropdown -->
                        <div class="mb-4">
                            <label for="payment_method" class="form-label required-field">Preferred Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <?php foreach ($payment_methods as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" 
                                        <?php echo (($_POST['payment_method'] ?? 'credit_card') == $value) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select your preferred payment method</div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="5" 
                                      placeholder="Tell us about your requirements, preferred viewing times, financing preferences, etc."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            <div class="form-text">Any specific requirements or questions?</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Purchase Inquiry
                            </button>
                            <a href="property-detail.php?id=<?php echo $property_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Property Details
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Property Summary -->
            <div class="col-lg-4">
                <div class="property-summary-card">
                    <h3 class="section-title">Property Summary</h3>
                    
                    <div class="property-image-container">
                        <img src="<?php echo htmlspecialchars($property['image'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'); ?>" 
                             alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'">
                    </div>
                    
                    <h4><?php echo htmlspecialchars($property['title'] ?? 'Untitled Property'); ?></h4>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt me-1"></i> 
                        <?php echo htmlspecialchars($property['location'] ?? 'Location not specified'); ?>
                    </p>
                    
                    <div class="property-details">
                        <div class="summary-item">
                            <span>Property Type:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars(ucfirst($property['type'] ?? 'Property')); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Bedrooms:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($property['bedrooms'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Bathrooms:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($property['bathrooms'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Area:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($property['area'] ?? 'N/A'); ?> sqft</span>
                        </div>
                        <div class="summary-item">
                            <span>Status:</span>
                            <span class="fw-medium badge bg-<?php echo ($property['status'] ?? 'available') == 'available' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars(ucfirst($property['status'] ?? 'Available')); ?>
                            </span>
                        </div>
                        <div class="summary-item total">
                            <span>Price:</span>
                            <span class="fw-bold"><?php echo $formatted_price; ?></span>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <h5 class="mb-3">Need Help?</h5>
                        <p class="text-muted mb-3">
                            <i class="fas fa-headset me-2"></i>Our team is here to assist you
                        </p>
                        <div class="d-grid gap-2">
                            <a href="tel:+15551234567" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                            <a href="mailto:info@housoraliving.com" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Purchase Inquiry Submission</h6>
                    <p>By submitting this purchase inquiry, you express genuine interest in purchasing the property and agree to be contacted by our sales team.</p>
                    
                    <h6>2. Property Information</h6>
                    <p>All property information is subject to verification. Prices and availability may change without notice.</p>
                    
                    <h6>3. Contact Information</h6>
                    <p>You agree to provide accurate contact information and consent to being contacted via phone, email, or SMS.</p>
                    
                    <h6>4. Privacy Policy</h6>
                    <p>Your personal information will be handled according to our privacy policy and will not be shared with third parties without consent.</p>
                    
                    <h6>5. Next Steps</h6>
                    <p>After submission, our team will contact you within 24-48 hours to discuss the purchase process, financing options, and property viewing.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="mb-4">Housora Living</h4>
                    <p class="text-light">Premium living experience for discerning individuals. Find your dream home with us.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="catalog.php" class="text-light text-decoration-none">Properties</a></li>
                        <li class="mb-2"><a href="about.php" class="text-light text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="index.php#contact" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="catalog.php" class="text-light text-decoration-none">Buy Property</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sell Property</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Property Valuation</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="mb-4">Contact Info</h5>
                    <ul class="list-unstyled text-light">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Premium Street, Luxury City
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone me-2"></i>
                            +1 (555) 123-4567
                        </li>
                        <li>
                            <i class="fas fa-envelope me-2"></i>
                            info@housoraliving.com
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-5" style="border-color: #444;">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 Housora Living. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light me-3">Privacy Policy</a>
                    <a href="#" class="text-light">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Form validation
        document.getElementById('purchaseForm').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value.trim();
            
            // Basic phone validation (optional)
            if (phoneValue && !/^[\d\s\-\+\(\)]+$/.test(phoneValue)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                phoneInput.focus();
                return false;
            }
            
            // Check terms acceptance
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('Please accept the Terms and Conditions');
                termsCheckbox.focus();
                return false;
            }
            
            // Optional: Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;
        });
        
        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0,3)}) ${value.slice(3)}`;
                } else if (value.length <= 10) {
                    value = `(${value.slice(0,3)}) ${value.slice(3,6)}-${value.slice(6)}`;
                } else {
                    value = `(${value.slice(0,3)}) ${value.slice(3,6)}-${value.slice(6,10)}`;
                }
            }
            
            e.target.value = value;
        });
        
        // Open terms modal if checkbox is not checked on submit attempt
        document.querySelector('button[type="submit"]').addEventListener('click', function(e) {
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                e.preventDefault();
                const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
                termsModal.show();
            }
        });
    </script>
</body>
</html>