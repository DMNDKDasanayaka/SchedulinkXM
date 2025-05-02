<?php require_once '../partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Exam Duty Allocations
                    </h4>
                    <div>
                        <button class="btn btn-light btn-sm me-2" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <a href="/reports/generate?type=excel" class="btn btn-light btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" class="row g-2">
                                <div class="col-md-5">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="from_date" name="from_date"
                                           value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                                </div>
                                <div class="col-md-5">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="to_date" name="to_date"
                                           value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-columns me-1"></i> Columns
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <?php foreach ($columnOptions as $col => $label): ?>
                                        <div class="dropdown-item">
                                            <div class="form-check">
                                                <input class="form-check-input toggle-col" type="checkbox"
                                                       id="col-<?= $col ?>" data-column="<?= $col ?>"
                                                       <?= in_array($col, $visibleColumns) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="col-<?= $col ?>">
                                                    <?= $label ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="allocationsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="col-date">Date</th>
                                    <th class="col-time">Time</th>
                                    <th class="col-subject">Subject</th>
                                    <th class="col-degree">Degree</th>
                                    <th class="col-students">Students</th>
                                    <th class="col-hall">Hall</th>
                                    <th class="col-supervisor">Supervisor</th>
                                    <th class="col-invigilators">Invigilators</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $allocations = $allocations ?? []; ?>
                                <?php foreach ($allocations as $allocation): ?>
                                    <tr>
                                        <td class="col-date"><?= date('D, M j', strtotime($allocation['date'])) ?></td>
                                        <td class="col-time"><?= date('g:i A', strtotime($allocation['start_time'])) ?> - <?= date('g:i A', strtotime($allocation['end_time'])) ?></td>
                                        <td class="col-subject"><?= htmlspecialchars($allocation['subject']) ?></td>
                                        <td class="col-degree"><span class="badge bg-primary"><?= htmlspecialchars($allocation['degree']) ?></span></td>
                                        <td class="col-students"><?= $allocation['regular_students'] + $allocation['repeat_students'] ?>
                                            <small class="text-muted">(<?= $allocation['regular_students'] ?>+<?= $allocation['repeat_students'] ?>)</small>
                                        </td>
                                        <td class="col-hall">
                                            <?php if ($allocation['hall_name']): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($allocation['hall_name']) ?> (<?= $allocation['capacity'] ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-supervisor">
                                            <?php if ($allocation['supervisor_name']): ?>
                                                <span class="d-block"><?= htmlspecialchars($allocation['supervisor_name']) ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($allocation['supervisor_dept']) ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-invigilators">
                                            <?php if (!empty($allocation['invigilators'])): ?>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($allocation['invigilators'] as $invigilator): ?>
                                                        <li><small><?= htmlspecialchars($invigilator['name']) ?></small>
                                                            <small class="text-muted">(<?= htmlspecialchars($invigilator['department']) ?>)</small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-actions">
                                            <div class="btn-group btn-group-sm">
                                                <a href="/exams/edit/<?= $allocation['exam_id'] ?>" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-secondary toggle-assignment"
                                                        data-exam-id="<?= $allocation['exam_id'] ?>"
                                                        title="Reassign Duties">
                                                    <i class="fas fa-user-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">Showing <?= count($allocations) ?> of <?= $totalAllocations ?? 0 ?> allocations</div>
                    <?php if (($totalPages ?? 1) > 1): ?>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= isset($filters['from_date']) ? '&from_date=' . $filters['from_date'] : '' ?><?= isset($filters['to_date']) ? '&to_date=' . $filters['to_date'] : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reassignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Reassign Exam Duties</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reassignmentContent">
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="saveReassignment">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Style -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .col-actions, .btn-group {
        display: none !important;
    }
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#allocationsTable').DataTable({
        responsive: true,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [8] }]
    });

    $('.toggle-col').on('change', function () {
        const colClass = $(this).data('column');
        const column = table.column(`.col-${colClass}`);
        column.visible(!column.visible());

        const visible = [];
        $('.toggle-col:checked').each(function () {
            visible.push($(this).data('column'));
        });
        localStorage.setItem('visibleColumns', JSON.stringify(visible));
    });

    const saved = JSON.parse(localStorage.getItem('visibleColumns') || '[]');
    if (saved.length > 0) {
        table.columns().visible(false);
        saved.forEach(col => {
            table.column(`.col-${col}`).visible(true);
            $(`#col-${col}`).prop('checked', true);
        });
    }

    $('.toggle-assignment').click(function () {
        const examId = $(this).data('exam-id');
        $('#reassignmentModal').modal('show');
        $('#reassignmentContent').load(`/exams/reassign/${examId} #reassignmentForm`);
    });

    $('#saveReassignment').click(function () {
        const form = $('#reassignmentForm');
        $.post(form.attr('action'), form.serialize())
            .done(() => {
                $('#reassignmentModal').modal('hide');
                location.reload();
            })
            .fail(xhr => {
                $('#reassignmentContent').html(xhr.responseText);
            });
    });
});
</script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
