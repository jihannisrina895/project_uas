<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/spreadsheet.php';
//require_once '../includes/functions.php';

// Check if user is admin
if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Temporary admin check
if($_SESSION['user_email'] != 'admin@housora.com') {
    header('Location: ../index.php');
    exit();
}

global $db;
$transactions = $db->getAll('transactions');
$message = '';
$message_type = '';

// Handle status update
if(isset($_GET['action']) && $_GET['action'] == 'update_status') {
    $id = $_GET['id'] ?? '';
    $new_status = $_GET['status'] ?? '';
    
    if($id && $new_status) {
        // Update transaction status
        if($db->update('transactions', $id, ['status' => $new_status])) {
            $message = 'Transaction status updated successfully';
            $message_type = 'success';
            // Refresh data
            $transactions = $db->getAll('transactions');
        } else {
            $message = 'Failed to update transaction status';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions | Housora Admin</title>
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

        .badge-pending { background-color: #ffc107; }
        .badge-completed { background-color: #198754; }
        .badge-cancelled { background-color: #dc3545; }
        .badge-processing { background-color: #0dcaf0; }
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
            <a href="properties.php">
                <i class="fas fa-building me-2"></i> Properties
            </a>
            <a href="users.php">
                <i class="fas fa-users me-2"></i> Users
            </a>
            <a href="transactions.php" class="active">
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
        <h1 class="mb-4">Manage Transactions</h1>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Property</th>
                                <th>Buyer</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Filter transactions if needed
                            $filtered_transactions = $transactions;
                            if(isset($_GET['status']) && !empty($_GET['status'])) {
                                $filtered_transactions = array_filter($transactions, function($t) {
                                    return $t['status'] == $_GET['status'];
                                });
                            }
                            
                            if(empty($filtered_transactions)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No transactions found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($filtered_transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo substr($transaction['id'], 0, 12) . '...'; ?></td>
                                    <td><?php echo $transaction['property_title']; ?></td>
                                    <td>
                                        <div><?php echo $transaction['user_name']; ?></div>
                                        <small class="text-muted"><?php echo $transaction['email']; ?></small>
                                    </td>
                                    <td><?php echo $transaction['total_amount']; ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $transaction['payment_method'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $transaction['status']; ?>">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Change Status
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="?action=update_status&id=<?php echo $transaction['id']; ?>&status=pending">
                                                        Set as Pending
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="?action=update_status&id=<?php echo $transaction['id']; ?>&status=processing">
                                                        Set as Processing
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="?action=update_status&id=<?php echo $transaction['id']; ?>&status=completed">
                                                        Set as Completed
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="?action=update_status&id=<?php echo $transaction['id']; ?>&status=cancelled">
                                                        Set as Cancelled
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <button class="btn btn-sm btn-info mt-1" data-bs-toggle="modal" data-bs-target="#viewTransactionModal" 
                                                onclick="viewTransactionDetails('<?php echo addslashes(json_encode($transaction)); ?>')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
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

    <!-- View Transaction Modal -->
    <div class="modal fade" id="viewTransactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetails">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewTransactionDetails(transactionJson) {
            const transaction = JSON.parse(transactionJson);
            
            let details = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Transaction Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td>${transaction.id}</td>
                            </tr>
                            <tr>
                                <td><strong>Property:</strong></td>
                                <td>${transaction.property_title}</td>
                            </tr>
                            <tr>
                                <td><strong>Amount:</strong></td>
                                <td>${transaction.total_amount}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-${transaction.status}">${transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1)}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>${new Date(transaction.created_at).toLocaleDateString()}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Buyer Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>${transaction.full_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${transaction.email}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>${transaction.phone || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td>${transaction.address || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td>${transaction.payment_method.charAt(0).toUpperCase() + transaction.payment_method.slice(1).replace('_', ' ')}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Additional Notes</h6>
                        <p>${transaction.notes || 'No additional notes provided.'}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('transactionDetails').innerHTML = details;
        }
    </script>
</body>
</html>