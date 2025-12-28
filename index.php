<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housora Living | Premium Real Estate</title>
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

        .card {
            border: 1px solid var(--medium-gray);
            border-radius: 16px;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
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

        .hero-section {
            background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                        url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            padding: 150px 0 100px;
            min-height: 80vh;
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

        .testimonial-card {
            background: var(--light-gray);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--medium-gray);
        }

        footer {
            background: var(--black);
            color: var(--white);
            padding: 60px 0 30px;
        }

        .footer-links a {
            color: var(--medium-gray);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
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
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my-purchases.php">My Purchases</a></li>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Find Your Dream Home with Housora Living</h1>
                    <p class="lead mb-4">Discover premium properties, luxury homes, and exclusive real estate opportunities. Your perfect living space awaits.</p>
                    <a href="catalog.php" class="btn btn-primary btn-lg me-3">Browse Properties</a>
                    <a href="#featured" class="btn btn-outline btn-lg">View Featured</a>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Search Properties</h4>
                            <form action="catalog.php" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="location" placeholder="Location">
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control" name="type">
                                            <option value="">Property Type</option>
                                            <option value="house">House</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="villa">Villa</option>
                                            <option value="condo">Condo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="min_price" placeholder="Min Price">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="max_price" placeholder="Max Price">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100">Search Now</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section id="featured" class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">Featured Properties</h2>
                    <p class="text-muted">Handpicked selection of premium properties</p>
                </div>
            </div>
            <div class="row g-4">
                <?php
                // Sample featured properties - in real app, fetch from database
                $featured_properties = [
                    [
                        'id' => 1,
                        'title' => 'Modern Luxury Villa',
                        'price' => '$1,250,000',
                        'location' => 'Beverly Hills, CA',
                        'type' => 'villa',
                        'bedrooms' => 5,
                        'bathrooms' => 4,
                        'area' => '3,500 sqft',
                        'image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Downtown Penthouse',
                        'price' => '$850,000',
                        'location' => 'New York, NY',
                        'type' => 'apartment',
                        'bedrooms' => 3,
                        'bathrooms' => 3,
                        'area' => '2,200 sqft',
                        'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
                    ],
                    [
                        'id' => 3,
                        'title' => 'Beachfront House',
                        'price' => '$2,100,000',
                        'location' => 'Miami, FL',
                        'type' => 'house',
                        'bedrooms' => 6,
                        'bathrooms' => 5,
                        'area' => '4,800 sqft',
                        'image' => 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
                    ]
                ];

                foreach($featured_properties as $property):
                ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="position-relative">
                            <img src="<?php echo $property['image']; ?>" class="card-img-top" alt="<?php echo $property['title']; ?>" style="height: 250px; object-fit: cover;">
                            <span class="property-badge"><?php echo ucfirst($property['type']); ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $property['title']; ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo $property['location']; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h4 text-accent"><?php echo $property['price']; ?></span>
                                <div class="text-muted">
                                    <small class="me-3"><i class="fas fa-bed me-1"></i> <?php echo $property['bedrooms']; ?> Bd</small>
                                    <small><i class="fas fa-bath me-1"></i> <?php echo $property['bathrooms']; ?> Ba</small>
                                </div>
                            </div>
                            <div class="d-grid">
                                <a href="property-detail.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="catalog.php" class="btn btn-outline btn-lg">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">Why Choose Housora Living</h2>
                    <p class="text-muted">Experience premium real estate service</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x text-accent"></i>
                        </div>
                        <h4 class="mb-3">Secure Transactions</h4>
                        <p class="text-muted">Bank-level security for all your real estate transactions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-search fa-2x text-accent"></i>
                        </div>
                        <h4 class="mb-3">Verified Properties</h4>
                        <p class="text-muted">Every property is thoroughly verified for quality and authenticity.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-headset fa-2x text-accent"></i>
                        </div>
                        <h4 class="mb-3">24/7 Support</h4>
                        <p class="text-muted">Our team is always available to assist you with any queries.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">What Our Clients Say</h2>
                    <p class="text-muted">Join thousands of satisfied homeowners</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4 fst-italic">"Housora Living helped us find our dream home in just two weeks. Exceptional service!"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Homeowner</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4 fst-italic">"As an investor, I appreciate their market insights. They helped me build a profitable portfolio."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Real Estate Investor</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <p class="mb-4 fst-italic">"The premium properties listed here are truly exceptional. Quality verification is unmatched."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Emily Rodriguez</h6>
                                <small class="text-muted">Architect</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Get In Touch</h2>
                <p class="text-muted">Have questions? We're here to help you.</p>
            </div>
        </div>

        <div class="row">
            <!-- Contact Info -->
            <div class="col-md-6 mb-4">
                <h4 class="fw-bold mb-3">Contact Info</h4>
                <p><i class="fas fa-map-marker-alt me-2"></i>123 Premium Street, Luxury City</p>
                <p><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</p>
                <p><i class="fas fa-envelope me-2"></i>info@housoraliving.com</p>
            </div>

            <!-- Contact Form -->
            <div class="col-md-6">
                <h4 class="fw-bold mb-3">Send a Message</h4>
                <form>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Your Name">
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Your Email">
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" rows="4" placeholder="Your Message"></textarea>
                    </div>
                    <button class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>


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
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php">Home</a></li>
                        <li class="mb-2"><a href="catalog.php">Properties</a></li>
                        <li class="mb-2"><a href="about.php">About Us</a></li>
                        <li class="mb-2"><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Services</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#">Buy Property</a></li>
                        <li class="mb-2"><a href="#">Sell Property</a></li>
                        <li class="mb-2"><a href="#">Rent Property</a></li>
                        <li class="mb-2"><a href="#">Property Valuation</a></li>
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
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]::not([href="about.php"])').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>