<?php require_once '../partials/header.php'; ?>

<div class="container mt-4">
    <h2>Add New Exam Hall</h2>
    
    <div class="card">
        <div class="card-body">
            <form action="/halls/store" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Hall Name</label>
                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           id="name" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                           id="capacity" name="capacity" min="1" value="<?= htmlspecialchars($data['capacity'] ?? '') ?>" required>
                    <?php if (isset($errors['capacity'])): ?>
                    <div class="invalid-feedback"><?= $errors['capacity'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="faculty" class="form-label">Faculty (Optional)</label>
                    <input type="text" class="form-control" id="faculty" name="faculty" 
                           value="<?= htmlspecialchars($data['faculty'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Save Hall</button>
                <a href="/halls" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>