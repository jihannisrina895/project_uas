<?php
// LOAD CONFIGURASI
require_once '../includes/config.php';
require_once '../includes/spreadsheet.php'; // File yang sudah diupdate

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get properties dari Google Sheets
$properties = getProperties();
$message = '';
$message_type = '';

// Handle actions
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? '';
    
    if($action == 'delete' && $id) {
        // Delete dari Google Sheets menggunakan global $db
        global $db;
        if($db->delete('properties', $id)) {
            $message = 'Property deleted successfully';
            $message_type = 'success';
            // Refresh properties list
            $properties = getProperties();
        } else {
            $message = 'Failed to delete property';
            $message_type = 'danger';
        }
    }
}

// Handle form submission for add/edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 'prop_' . time() . '_' . rand(1000, 9999);
    $title = $_POST['title'] ?? '';
    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $type = $_POST['type'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? '';
    $bathrooms = $_POST['bathrooms'] ?? '';
    $area = $_POST['area'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'available';
    $image = $_POST['image'] ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2';
    
    $property_data = [
        'id' => $id,
        'title' => $title,
        'price' => $price,
        'location' => $location,
        'type' => $type,
        'bedrooms' => $bedrooms,
        'bathrooms' => $bathrooms,
        'area' => $area,
        'description' => $description,
        'status' => $status,
        'image' => $image,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Check if editing existing
    $existing = false;
    foreach($properties as $property) {
        if($property['id'] == $id) {
            $existing = true;
            break;
        }
    }
    
    global $db;
    if($existing) {
        // Update existing
        if($db->update('properties', $id, $property_data)) {
            $message = 'Property updated successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to update property';
            $message_type = 'danger';
        }
    } else {
        // Insert new
        if($db->insert('properties', $property_data)) {
            $message = 'Property added successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to add property';
            $message_type = 'danger';
        }
    }
    
    // Refresh properties list
    $properties = getProperties();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties | Housora Admin</title>
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

        .property-image {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h3><i class="fas fa-home me-2"></i> Housora Admin</h3>
            <p class="text-white-50 mb-0">Welcome, <?php echo $_SESSION['username']; ?></p>
        </div>
        
        <nav>
            <a href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="properties.php" class="active">
                <i class="fas fa-building me-2"></i> Properties
            </a>
            <a href="users.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Properties</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                <i class="fas fa-plus me-2"></i> Add New Property
            </button>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($properties)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No properties found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($properties as $property): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $property['image']; ?>" class="property-image" alt="<?php echo $property['title']; ?>">
                                    </td>
                                    <td><?php echo $property['title']; ?></td>
                                    <td><?php echo ucfirst($property['type']); ?></td>
                                    <td><?php echo $property['location']; ?></td>
                                    <td><?php echo $property['price']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $property['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPropertyModal" 
                                                onclick="editProperty('<?php echo $property['id']; ?>', '<?php echo addslashes($property['title']); ?>', '<?php echo $property['price']; ?>', '<?php echo addslashes($property['location']); ?>', '<?php echo $property['type']; ?>', '<?php echo $property['bedrooms']; ?>', '<?php echo $property['bathrooms']; ?>', '<?php echo $property['area']; ?>', '<?php echo addslashes($property['description']); ?>', '<?php echo $property['status']; ?>', '<?php echo $property['image']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?action=delete&id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Property Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price *</label>
                                <input type="text" class="form-control" name="price" placeholder="$1,000,000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Property Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="condo">Condo</option>
                                    <option value="townhouse">Townhouse</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" name="bedrooms" min="1" value="3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" name="bathrooms" min="1" value="2">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Area (sqft)</label>
                                <input type="text" class="form-control" name="area" placeholder="2,500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="available">Available</option>
                                    <option value="sold">Sold</option>
                                    <option value="pending">Pending</option>
                                    <option value="rented">Rented</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image URL</label>
                                <input type="url" class="form-control" name="image" placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Property Title *</label>
                                <input type="text" class="form-control" name="title" id="edit_title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price *</label>
                                <input type="text" class="form-control" name="price" id="edit_price" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" id="edit_location" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Property Type *</label>
                                <select class="form-select" name="type" id="edit_type" required>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="condo">Condo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bedrooms</label>
                                <input type="number" class="form-control" name="bedrooms" id="edit_bedrooms">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bathrooms</label>
                                <input type="number" class="form-control" name="bathrooms" id="edit_bathrooms">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Area (sqft)</label>
                                <input type="text" class="form-control" name="area" id="edit_area">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status">
                                    <option value="available">Available</option>
                                    <option value="sold">Sold</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image URL</label>
                                <input type="url" class="form-control" name="image" id="edit_image">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProperty(id, title, price, location, type, bedrooms, bathrooms, area, description, status, image) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_location').value = location;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_bedrooms').value = bedrooms;
            document.getElementById('edit_bathrooms').value = bathrooms;
            document.getElementById('edit_area').value = area;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_image').value = image;
            
            var editModal = new bootstrap.Modal(document.getElementById('editPropertyModal'));
            editModal.show();
        }
    </script>
</body>
</html>