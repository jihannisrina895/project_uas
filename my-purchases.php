<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/spreadsheet.php';
//require_once 'includes/functions.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil semua transaksi user
global $db;
$allTransactions = $db->getAll('transactions');
$user_transactions = [];

// Filter transaksi berdasarkan user_id
foreach ($allTransactions as $transaction) {
    if (isset($transaction['user_id']) && $transaction['user_id'] == $_SESSION['user_id']) {
        // Ambil detail properti untuk setiap transaksi
        $property = getPropertyById($transaction['property_id']);
        if ($property) {
            $transaction['property_title'] = $property['title'] ?? 'Unknown Property';
            $transaction['total_amount'] = '$' . number_format($property['price'] ?? 0);
        } else {
            $transaction['property_title'] = 'Property Not Found';
            $transaction['total_amount'] = '$0';
        }
        
        // Tambahkan field default jika tidak ada
        $transaction['payment_method'] = $transaction['payment_method'] ?? 'credit_card';
        $transaction['status'] = $transaction['status'] ?? 'funding';
        $transaction['created_at'] = $transaction['created_at'] ?? date('Y-m-d H:i:s');
        
        $user_transactions[] = $transaction;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases | Housora Living</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php 
    $current_page = 'purchases';
    include 'includes/header.php'; 
    ?>

    <div class="container py-5">
        <h1 class="mb-4">My Purchases</h1>
        
        <?php if(empty($user_transactions)): ?>
            <div class="alert alert-info">
                <h4>No purchases yet</h4>
                <p>You haven't made any purchases yet. <a href="catalog.php" class="alert-link">Browse our properties</a> to get started.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($user_transactions as $transaction): ?>
                        <?php 
                            // Format payment method
                            $payment_method = $transaction['payment_method'] ?? 'credit_card';
                            if ($payment_method == 'credit_card') {
                                $payment_display = 'Credit Card';
                            } else {
                                $payment_display = ucfirst(str_replace('_', ' ', $payment_method));
                            }
                            
                            // Format date
                            $date = !empty($transaction['created_at']) ? date('M d, Y', strtotime($transaction['created_at'])) : 'N/A';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['property_title']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['total_amount']); ?></td>
                            <td><?php echo $payment_display; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    $status = $transaction['status'] ?? 'funding';
                                    switch($status) {
                                        case 'completed': echo 'success'; break;
                                        case 'pending': 
                                        case 'funding': echo 'warning'; break;
                                        case 'processing': echo 'info'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td><?php echo $date; ?></td>
                            <td>
                                <a href="property-detail.php?id=<?php echo $transaction['property_id']; ?>" class="btn btn-sm btn-primary">
                                    View Property
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>