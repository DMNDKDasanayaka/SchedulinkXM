<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Define permission check function
function hasPermission($requiredRole) {
    // Default role hierarchy
    $roleHierarchy = [
        'admin' => 3,
        'coordinator' => 2,
        'viewer' => 1
    ];
    
    $userRole = $_SESSION['user_role'] ?? 'viewer';
    return ($roleHierarchy[$userRole] ?? 0) >= ($roleHierarchy[$requiredRole] ?? 0);
}



require_once __DIR__ . '/../modules/payment_calculation.php';

$paymentCalculator = new PaymentCalculator($conn);
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_rates'])) {
        $newRates = [
            'supervisor' => floatval($_POST['supervisor_rate']),
            'invigilator' => floatval($_POST['invigilator_rate']),
            'hall_attendant' => floatval($_POST['attendant_rate'])
        ];
        
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
        
        // Save new rates - using separate statements for each rate
        $success = true;
        foreach ($newRates as $role => $rate) {
            $query = "INSERT INTO pay_rates (role_type, rate_amount, effective_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            
            // Create variables for binding
            $roleType = $role;
            $rateAmount = $rate;
            $date = $effectiveDate;
            
            $stmt->bind_param("sds", $roleType, $rateAmount, $date);
            
            if (!$stmt->execute()) {
                $success = false;
                error_log("Failed to update rate for $role: " . $conn->error);
                break;
            }
        }
        
        if ($success) {
            $message = '<div class="alert alert-success">Payment rates updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update some rates. Please check logs.</div>';
        }
    }
    
    if (isset($_POST['process_payments'])) {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        
        $result = $paymentCalculator->processBatchPayments($startDate, $endDate);
        
        if (isset($result['error'])) {
            $message = '<div class="alert alert-danger">' . $result['error'] . '</div>';
        } else {
            $message = '<div class="alert alert-success">Processed ' . $result['processed'] . 
                      ' payments. ' . ($result['errors'] ?? 0) . ' errors occurred.</div>';
        }
    }
}

// Get current rates
$currentRates = [];
try {
    $query = "SELECT role_type, rate_amount FROM pay_rates 
             WHERE effective_date <= CURDATE() 
             ORDER BY effective_date DESC";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $currentRates[$row['role_type']] = $row['rate_amount'];
    }
} catch (Exception $e) {
    error_log("Error loading payment rates: " . $e->getMessage());
    $currentRates = [
        'supervisor' => 1500,
        'invigilator' => 1000,
        'hall_attendant' => 800
    ];
}

// Get recent payments
try {
    $recentPayments = $paymentCalculator->generatePaymentReport(
        date('Y-m-d', strtotime('-30 days')),
        date('Y-m-d')
    );
} catch (Exception $e) {
    error_log("Error generating payment report: " . $e->getMessage());
    $recentPayments = [];
    $message = '<div class="alert alert-danger">Error loading payment records</div>';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="my-4">Payment Management</h2>
            <?php echo $message; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Configure Payment Rates</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Supervisor Rate (LKR)</label>
                                <input type="number" step="0.01" class="form-control" name="supervisor_rate" 
                                       value="<?php echo htmlspecialchars($currentRates['supervisor'] ?? '1500.00'); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Invigilator Rate (LKR)</label>
                                <input type="number" step="0.01" class="form-control" name="invigilator_rate" 
                                       value="<?php echo htmlspecialchars($currentRates['invigilator'] ?? '1000.00'); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Hall Attendant Rate (LKR)</label>
                                <input type="number" step="0.01" class="form-control" name="attendant_rate" 
                                       value="<?php echo htmlspecialchars($currentRates['hall_attendant'] ?? '800.00'); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Effective Date</label>
                            <input type="date" class="form-control" name="effective_date" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" name="update_rates" class="btn btn-primary">
                            Update Payment Rates
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Process Payments</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Start Date</label>
                                <input type="date" class="form-control" name="start_date" 
                                       value="<?php echo date('Y-m-d', strtotime('-1 month')); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="end_date" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <button type="submit" name="process_payments" class="btn btn-success">
                            Process Payments for Selected Period
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Recent Payment Records (Last 30 Days)</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPayments)): ?>
                        <div class="alert alert-info">No payment records found</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Lecturer</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Role</th>
                                        <th>Amount (LKR)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['lecturer_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['subject_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['date'] ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst($payment['role_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($payment['payment_amount']) ? number_format($payment['payment_amount'], 2) : '0.00'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo ($payment['payment_status'] ?? 'pending') === 'paid' ? 'success' : 
                                                     (($payment['payment_status'] ?? 'pending') === 'processed' ? 'info' : 'warning');
                                            ?>">
                                                <?php echo ucfirst($payment['payment_status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-secondary" onclick="window.print()">
                                Print Payment Report
                            </button>
                            <a href="export_payments.php?type=csv" class="btn btn-info">
                                Export to CSV
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>