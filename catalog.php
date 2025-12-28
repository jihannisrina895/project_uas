<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Google Sheets config
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php';

// Get properties from Google Sheets
global $db;
$all_properties = $db->getAll('properties');

// Filter properties based on search
$filtered_properties = $all_properties;

if(isset($_GET['location']) && !empty($_GET['location'])) {
    $location = strtolower(trim($_GET['location']));
    $filtered_properties = array_filter($filtered_properties, function($property) use ($location) {
        $prop_location = strtolower($property['location'] ?? '');
        return strpos($prop_location, $location) !== false;
    });
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $type = $_GET['type'];
    $filtered_properties = array_filter($filtered_properties, function($property) use ($type) {
        return ($property['type'] ?? '') == $type;
    });
}

if(isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
    $filtered_properties = array_filter($filtered_properties, function($property) use ($min_price) {
        $price = floatval($property['price'] ?? 0);
        return $price >= $min_price;
    });
}

if(isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $max_price = floatval($_GET['max_price']);
    $filtered_properties = array_filter($filtered_properties, function($property) use ($max_price) {
        $price = floatval($property['price'] ?? 0);
        return $price <= $max_price;
    });
}

if(isset($_GET['bedrooms']) && !empty($_GET['bedrooms'])) {
    $bedrooms = intval($_GET['bedrooms']);
    $filtered_properties = array_filter($filtered_properties, function($property) use ($bedrooms) {
        $prop_bedrooms = intval($property['bedrooms'] ?? 0);
        return $prop_bedrooms >= $bedrooms;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Catalog | Housora Living</title>
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
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--black);
            background-color: var(--white);
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
            padding: 10px 24px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--accent-light);
            border-color: var(--accent-light);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid var(--medium-gray);
            color: var(--black);
            padding: 10px 24px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .btn-outline:hover {
            border-color: var(--accent);
            background-color: var(--accent);
            color: var(--white);
        }

        .property-card {
            border: 1px solid var(--medium-gray);
            border-radius: 16px;
            transition: var(--transition);
            box-shadow: var(--shadow);
            height: 100%;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .property-image {
            height: 250px;
            overflow: hidden;
            border-radius: 16px 16px 0 0;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .property-card:hover .property-image img {
            transform: scale(1.05);
        }

        .property-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        .filter-card {
            background: var(--light-gray);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid var(--medium-gray);
        }

        .form-control {
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            padding: 12px 16px;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }
        
        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 12px;
        }
        
        .img-fallback {
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            height: 250px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                        <a class="nav-link active" href="catalog.php">Properties</a>
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
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold">Property Catalog</h1>
                <p class="text-muted">Browse our premium selection of properties</p>
            </div>
        </div>

        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-card sticky-top" style="top: 100px;">
                    <h5 class="mb-4">Filter Properties</h5>
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" 
                                   placeholder="City, State, Zip" 
                                   value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-control" name="type">
                                <option value="">All Types</option>
                                <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                                <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                                <option value="villa" <?php echo (isset($_GET['type']) && $_GET['type'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
                                <option value="condo" <?php echo (isset($_GET['type']) && $_GET['type'] == 'condo') ? 'selected' : ''; ?>>Condo</option>
                                <option value="townhouse" <?php echo (isset($_GET['type']) && $_GET['type'] == 'townhouse') ? 'selected' : ''; ?>>Townhouse</option>
                            </select>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Min Price</label>
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="$" 
                                       value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Max Price</label>
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="$" 
                                       value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Bedrooms</label>
                            <select class="form-control" name="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '1') ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '2') ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '3') ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                                <option value="5" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '5') ? 'selected' : ''; ?>>5+</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
                        <a href="catalog.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                    </form>
                </div>
            </div>

            <!-- Properties Grid -->
            <div class="col-lg-9">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-0">Showing <?php echo count($filtered_properties); ?> properties</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary active">Newest</button>
                            <button type="button" class="btn btn-outline-secondary">Price: Low to High</button>
                            <button type="button" class="btn btn-outline-secondary">Price: High to Low</button>
                        </div>
                    </div>
                </div>

                <?php if(empty($filtered_properties)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-home fa-4x text-muted mb-3"></i>
                        <h4>No properties found</h4>
                        <p class="text-muted">Try adjusting your search filters or check back later</p>
                        <a href="catalog.php" class="btn btn-primary mt-3">View All Properties</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($filtered_properties as $property): 
                            $id = $property['id'] ?? 0;
                            $title = htmlspecialchars($property['title'] ?? 'Untitled Property');
                            $price = isset($property['price']) ? '$' . number_format($property['price']) : 'Price upon request';
                            $location = htmlspecialchars($property['location'] ?? 'Location not specified');
                            $type = htmlspecialchars($property['type'] ?? 'property');
                            $bedrooms = $property['bedrooms'] ?? 'N/A';
                            $bathrooms = $property['bathrooms'] ?? 'N/A';
                            $image_url = $property['image'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                            $area = $property['area'] ?? 'N/A sqft';
                            $status = $property['status'] ?? 'available';
                            
                            // Determine status badge color
                            $status_color = 'secondary';
                            if($status == 'available') $status_color = 'success';
                            elseif($status == 'sold') $status_color = 'danger';
                            elseif($status == 'pending') $status_color = 'warning';
                            elseif($status == 'featured') $status_color = 'info';
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card">
                                <div class="property-image position-relative">
                                    <?php if(!empty($image_url)): ?>
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                         alt="<?php echo $title; ?>"
                                         onerror="this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                                    <?php else: ?>
                                    <div class="img-fallback">
                                        <i class="fas fa-home fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                    <span class="property-badge"><?php echo ucfirst($type); ?></span>
                                    <?php if($status != 'available'): ?>
                                    <span class="status-badge badge bg-<?php echo $status_color; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $title; ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo $location; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-accent fw-bold"><?php echo $price; ?></span>
                                        <div class="text-muted">
                                            <?php if($bedrooms != 'N/A'): ?>
                                            <small class="me-3"><i class="fas fa-bed me-1"></i> <?php echo $bedrooms; ?></small>
                                            <?php endif; ?>
                                            <?php if($bathrooms != 'N/A'): ?>
                                            <small><i class="fas fa-bath me-1"></i> <?php echo $bathrooms; ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if($area != 'N/A sqft'): ?>
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-vector-square me-1"></i> <?php echo $area; ?>
                                    </p>
                                    <?php endif; ?>
                                    <div class="d-grid">
                                        <a href="property-detail.php?id=<?php echo $id; ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
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
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="catalog.php" class="text-light text-decoration-none">Properties</a></li>
                        <li class="mb-2"><a href="about.php" class="text-light text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="index.php#contact" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Services</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="catalog.php" class="text-light text-decoration-none">Buy Property</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sell Property</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Rent Property</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sort functionality
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Here you would typically reload with sort parameter
                // For now, just change the button state
            });
        });
        
        // Auto-submit filter form on select change
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select[name="type"], select[name="bedrooms"]');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
</body>
</html>