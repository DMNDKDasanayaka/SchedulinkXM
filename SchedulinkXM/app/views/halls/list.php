<?php
session_start(); // Start the session
require_once '../partials/header.php';

require_once '../../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, capacity, faculty FROM halls");
    $stmt->execute();
    $halls = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $halls = [];
    $error = "Failed to fetch exam halls: " . $e->getMessage();
}

// Check if the user role is set in the session
$canEdit = isset($_SESSION['user_role']) && $_SESSION['user_role'] === '';
?>

<div class="container mt-4">
    <h2>Exam Halls</h2>
    
    <?php if ($canEdit): ?>
        <a href="/halls/create" class="btn btn-primary mb-3">Add New Hall</a>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Hall Name</th>
                <th>Capacity</th>
                <th>Faculty</th>
                <?php if ($canEdit): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($halls)): ?>
                <?php foreach ($halls as $hall): ?>
                    <tr>
                        <td><?= htmlspecialchars($hall['id']) ?></td>
                        <td><?= htmlspecialchars($hall['name']) ?></td>
                        <td><?= htmlspecialchars($hall['capacity']) ?></td>
                        <td><?= htmlspecialchars($hall['faculty'] ?? 'N/A') ?></td>
                        <?php if ($canEdit): ?>
                            <td>
                                <a href="/halls/edit/<?= $hall['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="/halls/delete/<?= $hall['id'] ?>" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this hall?');">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?= $canEdit ? '5' : '4' ?>" class="text-center">No exam halls available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../partials/footer.php'; ?>