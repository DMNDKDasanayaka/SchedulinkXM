<?php require_once '../partials/header.php'; ?>

<div class="container mt-4">
    <h2>Exam Schedule</h2>
    <a href="../exams/create.php" class="btn btn-primary mb-3">Add New Exam</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Degree</th>
                <th>Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($exams) && is_array($exams) && count($exams) > 0): ?>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?= htmlspecialchars($exam['date']) ?></td>
                    <td><?= htmlspecialchars($exam['start_time']) ?> - <?= htmlspecialchars($exam['end_time']) ?></td>
                    <td><?= htmlspecialchars($exam['subject']) ?></td>
                    <td><?= htmlspecialchars($exam['degree']) ?></td>
                    <td><?= htmlspecialchars($exam['regular_students'] + $exam['repeat_students']) ?></td>
                    <td>
                        <a href="/exams/edit/<?= $exam['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form action="/exams/delete/<?= $exam['id'] ?>" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No exams found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../partials/footer.php'; ?>
