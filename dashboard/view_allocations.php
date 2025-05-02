<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Simple permission check function
function hasPermission($requiredRole) {
    // Default to viewer if role not set
    $userRole = $_SESSION['user_role'] ?? 'viewer';
    
    // Define role hierarchy
    $roleHierarchy = [
        'admin' => 4,
        'coordinator' => 3,
        'editor' => 2,
        'viewer' => 1
    ];
    
    // Check if user has sufficient privileges
    return ($roleHierarchy[$userRole] ?? 0) >= ($roleHierarchy[$requiredRole] ?? 0);
}

// Check permissions - at least viewer required
if (!hasPermission('viewer')) {
    header("Location: /dashboard/");
    exit();
}

require_once __DIR__ . '/../modules/allocation_logic.php';

$allocationManager = new AllocationManager($conn);
$message = '';

// Handle allocation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_allocation'])) {
        $allocationId = intval($_POST['allocation_id']);
        
        // Verify user has permission to delete
        if (!hasPermission('coordinator')) {
            $message = '<div class="alert alert-danger">You do not have permission to delete allocations</div>';
        } else {
            $query = "DELETE FROM allocations WHERE allocation_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $allocationId);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Allocation deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to delete allocation: ' . $conn->error . '</div>';
            }
        }
    }
}

// Get all allocations
$query = "SELECT a.*, e.subject_name, e.date, e.start_time, e.end_time, 
                 h.hall_name, l.name AS supervisor_name
          FROM allocations a
          JOIN exams e ON a.exam_id = e.exam_id
          JOIN exam_halls h ON a.hall_id = h.hall_id
          JOIN lecturers l ON a.supervisor_id = l.lecturer_id
          ORDER BY e.date, e.start_time";
$result = $conn->query($query);
$allocations = [];
while ($row = $result->fetch_assoc()) {
    $allocations[] = $row;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="my-4">View Allocations</h2>
            <?php echo $message; ?>
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Current Allocations</h4>
                        <?php if (hasPermission('coordinator')): ?>
                            <a href="auto_allocate.php" class="btn btn-primary">Auto Allocate All</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Hall</th>
                                    <th>Supervisor</th>
                                    <th>Staff Hierarchy</th>
                                    <?php if (hasPermission('coordinator')): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocations as $allocation): 
                                    $hierarchy = $allocationManager->getRankHierarchyInfo($allocation['allocation_id']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($allocation['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['date']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['start_time'] . ' - ' . $allocation['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['hall_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['supervisor_name']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-toggle="modal" 
                                                data-target="#hierarchyModal<?php echo $allocation['allocation_id']; ?>">
                                            View Hierarchy
                                        </button>
                                        
                                        <!-- Hierarchy Modal -->
                                        <div class="modal fade" id="hierarchyModal<?php echo $allocation['allocation_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Staff Rank Hierarchy</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>Supervisor</h6>
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <h5><?php echo htmlspecialchars($hierarchy['supervisor']['name']); ?></h5>
                                                                <p class="mb-1">Rank: <?php echo $hierarchy['supervisor']['rank']; ?></p>
                                                                <p class="mb-0">Role: Supervisor</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <h6>Invigilators</h6>
                                                        <?php foreach ($hierarchy['invigilators'] as $invigilator): ?>
                                                        <div class="card mb-2">
                                                            <div class="card-body">
                                                                <h5><?php echo htmlspecialchars($invigilator['name']); ?></h5>
                                                                <p class="mb-1">Rank: <?php echo $invigilator['rank']; ?></p>
                                                                <p class="mb-0">Role: Invigilator</p>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                        
                                                        <?php if ($this->validateRankHierarchy($hierarchy['supervisor']['rank'], array_column($hierarchy['invigilators'], 'rank'))): ?>
                                                        <div class="alert alert-success mt-3">
                                                            <i class="fas fa-check-circle"></i>
                                                            Rank hierarchy rules are properly maintained.
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="alert alert-warning mt-3">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Rank hierarchy issues detected!
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php if (hasPermission('coordinator')): ?>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="allocation_id" value="<?php echo $allocation['allocation_id']; ?>">
                                            <button type="submit" name="delete_allocation" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this allocation?')">
                                                Delete
                                            </button>
                                        </form>
                                        <a href="edit_allocation.php?id=<?php echo $allocation['allocation_id']; ?>" 
                                           class="btn btn-sm btn-warning">Edit</a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>