<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/spreadsheet.php';

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get data for reports
global $db;
$properties = $db->getAll('properties');
$users = $db->getAll('users');
$transactions = $db->getAll('transactions');

// Calculate statistics
$total_sales = 0;
$completed_transactions = 0;
$monthly_sales = [];

foreach($transactions as $transaction) {
    if(($transaction['status'] ?? 'pending') == 'completed') {
        $total_sales += floatval($transaction['total_amount'] ?? 0);
        $completed_transactions++;
        
        // Group by month
        $month = date('M Y', strtotime($transaction['created_at'] ?? 'now'));
        if(!isset($monthly_sales[$month])) {
            $monthly_sales[$month] = 0;
        }
        $monthly_sales[$month] += floatval($transaction['total_amount'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Housora Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .admin-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffd700;
            color: #000;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4 position-relative">
            <h3><i class="fas fa-home me-2"></i> Housora Admin</h3>
            <p class="text-white-50 mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
            <div class="admin-badge">
                <i class="fas fa-crown"></i> ADMIN
            </div>
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
            <a href="transactions.php">
                <i class="fas fa-exchange-alt me-2"></i> Transactions
            </a>
            <a href="reports.php" class="active">
                <i class="fas fa-chart-bar me-2"></i> Reports
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
        <h1 class="mb-4">Sales Reports</h1>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3>$<?php echo number_format($total_sales, 2); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3><?php echo $completed_transactions; ?></h3>
                        <p>Completed Transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3><?php echo count($properties); ?></h3>
                        <p>Properties Listed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3><?php echo count($users); ?></h3>
                        <p>Registered Users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Monthly Sales</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Transaction Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Generate Report</h5>
            </div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType">
                            <option value="sales">Sales Report</option>
                            <option value="transactions">Transactions Report</option>
                            <option value="properties">Properties Report</option>
                            <option value="users">Users Report</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100">
                            <i class="fas fa-download me-1"></i> Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Monthly Sales Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Sales Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Total Sales</th>
                                <th>Number of Transactions</th>
                                <th>Average Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($monthly_sales)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No sales data available</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                arsort($monthly_sales);
                                foreach($monthly_sales as $month => $sales): 
                                    $month_transactions = array_filter($transactions, function($transaction) use ($month) {
                                        return date('M Y', strtotime($transaction['created_at'] ?? 'now')) == $month && 
                                               ($transaction['status'] ?? 'pending') == 'completed';
                                    });
                                    $count = count($month_transactions);
                                    $average = $count > 0 ? $sales / $count : 0;
                                ?>
                                <tr>
                                    <td><?php echo $month; ?></td>
                                    <td>$<?php echo number_format($sales, 2); ?></td>
                                    <td><?php echo $count; ?></td>
                                    <td>$<?php echo number_format($average, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const monthlySalesData = <?php echo json_encode(array_values($monthly_sales)); ?>;
        const monthlyLabels = <?php echo json_encode(array_keys($monthly_sales)); ?>;
        
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Sales ($)',
                    data: monthlySalesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Transaction Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        
        <?php
        $status_counts = [
            'completed' => 0,
            'pending' => 0,
            'processing' => 0,
            'cancelled' => 0
        ];
        
        foreach($transactions as $transaction) {
            $status = $transaction['status'] ?? 'pending';
            if(isset($status_counts[$status])) {
                $status_counts[$status]++;
            } else {
                $status_counts[$status] = 1;
            }
        }
        ?>
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Processing', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $status_counts['completed']; ?>,
                        <?php echo $status_counts['pending']; ?>,
                        <?php echo $status_counts['processing']; ?>,
                        <?php echo $status_counts['cancelled']; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 205, 86, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>