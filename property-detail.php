<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Google Sheets config
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php';

if(!isset($_GET['id'])) {
    header('Location: catalog.php');
    exit();
}

$property_id = $_GET['id'];

// Get properties from Google Sheets
global $db;
$all_properties = $db->getAll('properties');

// Find the specific property
$property = null;
foreach($all_properties as $p) {
    if(isset($p['id']) && $p['id'] == $property_id) {
        $property = $p;
        break;
    }
}

if(!$property) {
    header('Location: catalog.php');
    exit();
}

// Format property data
$id = $property['id'] ?? 0;
$title = htmlspecialchars($property['title'] ?? 'Untitled Property');
$price = isset($property['price']) ? '$' . number_format($property['price']) : 'Price upon request';
$location = htmlspecialchars($property['location'] ?? 'Location not specified');
$type = htmlspecialchars($property['type'] ?? 'property');
$bedrooms = $property['bedrooms'] ?? 'N/A';
$bathrooms = $property['bathrooms'] ?? 'N/A';
$image_url = $property['image'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
$area = $property['area'] ?? 'N/A sqft';
$status = $property['status'] ?? 'available';
$description = $property['description'] ?? 'No description available.';
$garage = $property['garage'] ?? 'Not specified';
$year_built = $property['year_built'] ?? 'Not specified';

// Get related properties (same type)
$related_properties = array_filter($all_properties, function($p) use ($property) {
    return ($p['type'] ?? '') == ($property['type'] ?? '') && 
           ($p['id'] ?? '') != ($property['id'] ?? '') &&
           ($p['status'] ?? 'available') == 'available';
});

$related_properties = array_slice($related_properties, 0, 3);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Housora Living</title>
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

        .property-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('<?php echo htmlspecialchars($image_url); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0 60px;
            margin-top: 70px;
        }

        .property-header h1 {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .property-image-main {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .property-details-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--medium-gray);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .price-tag {
            font-size: 42px;
            font-weight: bold;
            color: var(--accent);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: var(--light-gray);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 20px;
            margin-right: 15px;
        }

        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 18px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--accent-light);
            border-color: var(--accent-light);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid var(--accent);
            color: var(--accent);
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 18px;
            transition: var(--transition);
        }

        .btn-outline:hover {
            background-color: var(--accent);
            color: white;
            transform: translateY(-2px);
        }

        .property-feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .gallery-image {
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
            height: 120px;
        }

        .gallery-image:hover {
            transform: scale(1.05);
        }

        .gallery-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: var(--accent);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--gray);
        }
        
        .img-fallback-large {
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            height: 400px;
            border-radius: 16px;
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

    <!-- Property Header -->
    <div class="property-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3"><?php echo $title; ?></h1>
                    <p class="lead mb-4">
                        <i class="fas fa-map-marker-alt me-2"></i> <?php echo $location; ?>
                    </p>
                    <div class="d-flex align-items-center">
                        <span class="price-tag me-4"><?php echo $price; ?></span>
                        <span class="badge bg-light text-dark fs-6"><?php echo ucfirst($type); ?></span>
                        <?php if($status != 'available'): ?>
                        <span class="badge bg-<?php echo $status == 'sold' ? 'danger' : 'warning'; ?> ms-2 fs-6">
                            <?php echo ucfirst($status); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Details -->
    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="catalog.php">Properties</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo substr($title, 0, 30) . (strlen($title) > 30 ? '...' : ''); ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Gallery -->
                <div class="row g-3 mb-5">
                    <div class="col-12">
                        <div class="property-image-main">
                            <?php if(!empty($image_url)): ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 alt="<?php echo $title; ?>" 
                                 class="img-fluid rounded"
                                 onerror="this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'">
                            <?php else: ?>
                            <div class="img-fallback-large">
                                <i class="fas fa-home fa-5x"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php 
                    // Sample gallery images (in real app, you might have multiple images per property)
                    $gallery_images = [
                        'https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                        'https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
                    ];
                    
                    foreach($gallery_images as $index => $image): 
                    ?>
                    <div class="col-md-4">
                        <div class="gallery-image">
                            <img src="<?php echo $image; ?>" 
                                 class="img-fluid rounded" 
                                 alt="Gallery <?php echo $index + 1; ?>"
                                 onclick="openImageModal('<?php echo $image; ?>')">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Description -->
                <div class="property-details-card mb-4">
                    <h3 class="mb-4">Property Description</h3>
                    <div class="property-description">
                        <?php echo nl2br(htmlspecialchars($description)); ?>
                    </div>
                    
                    <h4 class="mt-5 mb-4">Key Features</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Property Type</h6>
                                    <p class="text-muted mb-0"><?php echo ucfirst($type); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Total Area</h6>
                                    <p class="text-muted mb-0"><?php echo $area; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Bedrooms</h6>
                                    <p class="text-muted mb-0"><?php echo $bedrooms; ?> Bedrooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-bath"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Bathrooms</h6>
                                    <p class="text-muted mb-0"><?php echo $bathrooms; ?> Bathrooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Garage</h6>
                                    <p class="text-muted mb-0"><?php echo $garage; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-feature">
                                <div class="feature-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Year Built</h6>
                                    <p class="text-muted mb-0"><?php echo $year_built; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="property-details-card mb-4">
                    <h3 class="mb-4">Amenities</h3>
                    <div class="row">
                        <?php 
                        // Sample amenities (in real app, store this in database)
                        $amenities = [
                            'Swimming Pool', 'Gym', 'Garden', 'Security System',
                            'Central Heating', 'Air Conditioning', 'Parking', 'Balcony',
                            'Fireplace', 'Walk-in Closet', 'Hardwood Floors', 'Modern Kitchen'
                        ];
                        
                        foreach($amenities as $amenity): 
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check text-success me-3"></i>
                                <span><?php echo $amenity; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Action Buttons -->
                <div class="property-details-card mb-4">
                    <h4 class="mb-4">Interested in this property?</h4>
                    
                    <?php if($status == 'available'): ?>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="buy-form.php?property_id=<?php echo $id; ?>" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-shopping-cart me-2"></i> Make Purchase Inquiry
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=buy-form.php?property_id=<?php echo $id; ?>" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Purchase
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 mb-3" disabled>
                            <i class="fas fa-ban me-2"></i> <?php echo ucfirst($status); ?> - Not Available
                        </button>
                    <?php endif; ?>
                    
                    <a href="catalog.php" class="btn btn-outline w-100 mb-3">
                        <i class="fas fa-arrow-left me-2"></i> Back to Properties
                    </a>
                    
                    <button class="btn btn-light w-100 mb-3" onclick="addToFavorites()">
                        <i class="fas fa-heart me-2"></i> Save to Favorites
                    </button>
                    
                    <button class="btn btn-light w-100" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="fas fa-share-alt me-2"></i> Share Property
                    </button>
                </div>

                <!-- Property Summary -->
                <div class="property-details-card mb-4">
                    <h4 class="mb-4">Property Summary</h4>
                    <table class="table">
                        <tr>
                            <td>Property ID:</td>
                            <td class="text-end"><?php echo strtoupper(substr($id, 0, 8)); ?></td>
                        </tr>
                        <tr>
                            <td>Price:</td>
                            <td class="text-end fw-bold"><?php echo $price; ?></td>
                        </tr>
                        <tr>
                            <td>Type:</td>
                            <td class="text-end"><?php echo ucfirst($type); ?></td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td class="text-end">
                                <span class="badge bg-<?php echo $status == 'available' ? 'success' : ($status == 'sold' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Bedrooms:</td>
                            <td class="text-end"><?php echo $bedrooms; ?></td>
                        </tr>
                        <tr>
                            <td>Bathrooms:</td>
                            <td class="text-end"><?php echo $bathrooms; ?></td>
                        </tr>
                        <tr>
                            <td>Area:</td>
                            <td class="text-end"><?php echo $area; ?></td>
                        </tr>
                        <tr>
                            <td>Garage:</td>
                            <td class="text-end"><?php echo $garage; ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Contact Agent -->
                <div class="property-details-card" id="contact-agent">
                    <h4 class="mb-4">Contact Agent</h4>
                    <div class="text-center mb-4">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" 
                             alt="Agent" class="rounded-circle mb-3" width="100" height="100">
                        <h5>John Smith</h5>
                        <p class="text-muted">Senior Property Agent</p>
                    </div>
                    
                    <div class="mb-3">
                        <i class="fas fa-phone me-2"></i> +1 (555) 123-4567
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-envelope me-2"></i> john@housoraliving.com
                    </div>
                    
                    <form class="mt-4" id="contactForm">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" class="form-control" placeholder="Your Phone">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="3" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        <?php if(!empty($related_properties)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Similar Properties</h3>
                <div class="row g-4">
                    <?php foreach($related_properties as $related): 
                        $rel_id = $related['id'] ?? 0;
                        $rel_title = htmlspecialchars($related['title'] ?? 'Untitled Property');
                        $rel_price = isset($related['price']) ? '$' . number_format($related['price']) : 'Price upon request';
                        $rel_location = htmlspecialchars($related['location'] ?? 'Location not specified');
                        $rel_type = htmlspecialchars($related['type'] ?? 'property');
                        $rel_image = $related['image'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                    ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <img src="<?php echo htmlspecialchars($rel_image); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $rel_title; ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $rel_title; ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i> <?php echo $rel_location; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-accent"><?php echo $rel_price; ?></span>
                                    <a href="property-detail.php?id=<?php echo $rel_id; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <a href="#" class="btn btn-primary btn-lg">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-info btn-lg text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-danger btn-lg">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-success btn-lg">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                    <div class="mt-4">
                        <label class="form-label">Share Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control" 
                                   value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" 
                                   id="shareLink" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyShareLink()">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center p-0">
                    <img src="" id="modalImage" class="img-fluid" alt="Property Image">
                </div>
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
            <hr class="my-4" style="border-color: #444;">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Housora Living. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyShareLink() {
            const shareLink = document.getElementById('shareLink');
            shareLink.select();
            shareLink.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
        
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
        
        function addToFavorites() {
            alert('Added to favorites!');
            // In a real app, you would save to localStorage or send to server
        }
        
        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you! Your message has been sent. We will contact you shortly.');
            this.reset();
        });
        
        // Smooth scroll to contact agent section
        document.querySelectorAll('a[href="#contact-agent"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('contact-agent').scrollIntoView({ 
                    behavior: 'smooth' 
                });
            });
        });
    </script>
</body>
</html>