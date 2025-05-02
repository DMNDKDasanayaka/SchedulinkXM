<?php require_once 'partials/header.php'; ?>

<div class="container-fluid mt-4">

    <div class="row">
        <!-- Quick Stats Cards -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Exams</h5>
                    <h2 class="card-text"><?= $stats['upcoming_exams'] ?? 0 ?></h2>
                    <a href="exams/list.php" class="text-white">View All</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Available Halls</h5>
                    <h2 class="card-text"><?= $stats['available_halls'] ?? 0 ?></h2>
                    <a href="halls/list.php" class="text-white">Manage Halls</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Registered Lecturers</h5>
                    <h2 class="card-text"><?= $stats['registered_lecturers'] ?? 0 ?></h2>
                    <a href="lecturers/list.php" class="text-white">View All</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Allocations</h5>
                    <h2 class="card-text"><?= $stats['pending_allocations'] ?? 0 ?></h2>
                    <a href="reports/allocations.php" class="text-white">Review</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Exams Section -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Upcoming Exams (Next 7 Days)</h5>
                <a href="exams/create.php" class="btn btn-sm btn-light">Add New Exam</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($upcomingExams)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Students</th>
                                <th>Hall</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingExams as $exam): ?>
                            <tr>
                                <td><?= date('D, M j', strtotime($exam['date'])) ?></td>
                                <td><?= date('g:i A', strtotime($exam['start_time'])) ?> - <?= date('g:i A', strtotime($exam['end_time'])) ?></td>
                                <td><?= htmlspecialchars($exam['subject']) ?></td>
                                <td><?= $exam['regular_students'] + $exam['repeat_students'] ?></td>
                                <td>
                                    <?php if ($exam['hall_name']): ?>
                                        <span class="badge bg-primary"><?= $exam['hall_name'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($exam['supervisor_id']): ?>
                                        <span class="badge bg-success">Assigned</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/exams/edit/<?= $exam['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <?php if (!$exam['supervisor_id']): ?>
                                        <a href="/reports/allocations?exam_id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-success">Assign</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No upcoming exams in the next 7 days.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Alerts Section -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Capacity Warnings</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($capacityWarnings)): ?>
                        <ul class="list-group">
                            <?php foreach ($capacityWarnings as $warning): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($warning['subject']) ?></strong><br>
                                    <small><?= date('D, M j', strtotime($warning['date'])) ?> |
                                        <?= $warning['student_count'] ?> students |
                                        <?= $warning['available_capacity'] ?> seats available</small>
                                </div>
                                <a href="/reports/allocations?exam_id=<?= $warning['exam_id'] ?>" class="btn btn-sm btn-danger">Resolve</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-success">No capacity issues detected.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Unassigned Duties</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($unassignedDuties)): ?>
                        <ul class="list-group">
                            <?php foreach ($unassignedDuties as $duty): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($duty['subject']) ?></strong><br>
                                    <small><?= date('D, M j', strtotime($duty['date'])) ?> |
                                        Needs <?= $duty['required_invigilators'] ?> invigilators</small>
                                </div>
                                <a href="/reports/allocations?exam_id=<?= $duty['exam_id'] ?>" class="btn btn-sm btn-warning">Assign</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-success">All duties are properly assigned.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Log -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($recentActivity)): ?>
                <div class="timeline">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-badge <?= $activity['type_class'] ?>"></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h6 class="timeline-title"><?= htmlspecialchars($activity['title']) ?></h6>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-clock"></i> <?= $activity['time_ago'] ?> by <?= htmlspecialchars($activity['user']) ?>
                                </p>
                            </div>
                            <div class="timeline-body">
                                <p><?= htmlspecialchars($activity['description']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No recent activity to display.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 50px;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 15px;
    }
    .timeline-badge {
        position: absolute;
        left: -40px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        color: white;
    }
    .timeline-badge.primary { background-color: #0d6efd; }
    .timeline-badge.success { background-color: #198754; }
    .timeline-badge.warning { background-color: #ffc107; }
    .timeline-badge.danger { background-color: #dc3545; }
    .timeline-badge.info { background-color: #0dcaf0; }
    .timeline-panel {
        position: relative;
        background: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
</style>

<?php require_once 'partials/footer.php'; ?>
