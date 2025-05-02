<?php
require_once '../includes/header.php';
require_once '../includes/db_connect.php';

// Check permissions
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'coordinator') {
    header("Location: ../dashboard/");
    exit;
}

// Get all exams
$query = "SELECT e.*, l.name as setter_name 
          FROM exams e 
          LEFT JOIN lecturers l ON e.paper_setter_id = l.lecturer_id
          ORDER BY e.date, e.start_time";
$exams = $db->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2>Exam Schedule</h2>
    
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal">
            Add New Exam
        </button>
        <button class="btn btn-success" id="runAllocation">
            Run Auto Allocation
        </button>
    </div>
    
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Degree</th>
                <th>Students</th>
                <th>Paper Setter</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?= date('Y-m-d', strtotime($exam['date'])) ?></td>
                <td><?= date('H:i', strtotime($exam['start_time'])) ?> - <?= date('H:i', strtotime($exam['end_time'])) ?></td>
                <td><?= htmlspecialchars($exam['subject_name']) ?> (<?= htmlspecialchars($exam['subject_code']) ?>)</td>
                <td><?= htmlspecialchars($exam['degree']) ?></td>
                <td><?= $exam['student_count'] + $exam['repeaters'] ?></td>
                <td><?= htmlspecialchars($exam['setter_name'] ?? 'Not assigned') ?></td>
                <td>
                    <?php 
                    $allocated = $db->query("SELECT COUNT(*) FROM allocations WHERE exam_id = {$exam['exam_id']}")->fetch_row()[0];
                    echo $allocated > 0 ? '<span class="badge bg-success">Allocated</span>' : '<span class="badge bg-warning">Pending</span>';
                    ?>
                </td>
                <td>
                    <a href="view_allocation.php?exam_id=<?= $exam['exam_id'] ?>" class="btn btn-sm btn-info">View</a>
                    <button class="btn btn-sm btn-warning edit-exam" data-id="<?= $exam['exam_id'] ?>">Edit</button>
                    <button class="btn btn-sm btn-danger delete-exam" data-id="<?= $exam['exam_id'] ?>">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Exam Modal -->
<div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExamModalLabel">Add New Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="examForm" action="../modules/save_exam.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="subjectCode" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subjectCode" name="subjectCode" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subjectName" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subjectName" name="subjectName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="degree" class="form-label">Degree Program</label>
                            <select class="form-select" id="degree" name="degree" required>
                                <option value="">Select Degree</option>
                                <option value="ANS">Animal Science</option>
                                <option value="AQT">Aquatic Technology</option>
                                <option value="EAG">Export Agriculture</option>
                                <option value="TEA">Tea Technology</option>
                                <option value="PLT">Plantation Management</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="paperSetter" class="form-label">Paper Setter</label>
                            <select class="form-select" id="paperSetter" name="paperSetter">
                                <option value="">Select Paper Setter</option>
                                <?php
                                $lecturers = $db->query("SELECT lecturer_id, name FROM lecturers ORDER BY name");
                                while ($row = $lecturers->fetch_assoc()) {
                                    echo "<option value='{$row['lecturer_id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="examDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="examDate" name="examDate" required>
                        </div>
                        <div class="col-md-4">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="startTime" name="startTime" required>
                        </div>
                        <div class="col-md-4">
                            <label for="endTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="endTime" name="endTime" required>
                        </div>
                        <div class="col-md-6">
                            <label for="studentCount" class="form-label">Enrolled Students</label>
                            <input type="number" class="form-control" id="studentCount" name="studentCount" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="repeaters" class="form-label">Repeat Students</label>
                            <input type="number" class="form-control" id="repeaters" name="repeaters" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Run allocation
    $('#runAllocation').click(function() {
        if (confirm('Are you sure you want to run auto allocation for all unallocated exams?')) {
            $.post('../modules/run_allocation.php', function(response) {
                if (response.success) {
                    alert('Allocation completed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }).fail(function() {
                alert('Error running allocation');
            });
        }
    });
    
    // Other JS for edit/delete functionality...
});
</script>