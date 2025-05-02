<?php require_once '../partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users me-2"></i>Lecturers
                        </h4>
                        <div>
                            <a href="../lecturers/create.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Add Lecturer
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <?php
                        // Ensure variables are set to avoid warnings
                        $lecturers = $lecturers ?? [];
                        $totalLecturers = $totalLecturers ?? count($lecturers);
                        $totalPages = $totalPages ?? 1;
                        $currentPage = $currentPage ?? 1;
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="lecturersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Department</th>
                                    <th>Faculty</th>
                                    <th>Rank</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lecturers as $lecturer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($lecturer['id']) ?></td>
                                    <td><?= htmlspecialchars($lecturer['name']) ?></td>
                                    <td><?= htmlspecialchars($lecturer['designation']) ?></td>
                                    <td><?= htmlspecialchars($lecturer['department']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $lecturer['faculty'] === 'SCI' ? 'primary' : 
                                            ($lecturer['faculty'] === 'ENG' ? 'info' : 
                                            ($lecturer['faculty'] === 'ART' ? 'warning' : 
                                            ($lecturer['faculty'] === 'COM' ? 'success' : 'secondary'))) ?>">
                                            <?= htmlspecialchars($lecturer['faculty']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($lecturer['rank']) ?></td>
                                    <td>
                                        <?php if ($lecturer['availability']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/lecturers/edit/<?= $lecturer['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="/lecturers/delete/<?= $lecturer['id'] ?>" method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this lecturer?');">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div class="text-muted">
                            Showing <?= count($lecturers) ?> of <?= $totalLecturers ?> lecturers
                        </div>
                        <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                                    <a class="page-link" href="/lecturers?page=<?= $i ?>"><?= $i ?></a>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#lecturersTable').DataTable({
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [7] } // Disable sorting for actions column
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search lecturers...",
            }
        });
    }
});
</script>

<?php require_once '../partials/footer.php'; ?>
