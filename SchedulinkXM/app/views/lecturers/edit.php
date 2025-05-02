<?php require_once '../partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>Edit Lecturer
                        </h4>
                        <a href="/lecturers" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Lecturers
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form action="/lecturers/update/<?= $lecturer['id'] ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           id="name" name="name" value="<?= htmlspecialchars($lecturer['name'] ?? '') ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                                    <select class="form-select <?= isset($errors['designation']) ? 'is-invalid' : '' ?>" 
                                            id="designation" name="designation" required>
                                        <option value="">Select Designation</option>
                                        <option value="Professor" <?= $lecturer['designation'] === 'Professor' ? 'selected' : '' ?>>Professor</option>
                                        <option value="Senior Lecturer Gr (I)" <?= $lecturer['designation'] === 'Senior Lecturer Gr (I)' ? 'selected' : '' ?>>Senior Lecturer (Grade I)</option>
                                        <option value="Senior Lecturer Gr (II)" <?= $lecturer['designation'] === 'Senior Lecturer Gr (II)' ? 'selected' : '' ?>>Senior Lecturer (Grade II)</option>
                                        <option value="Lecturer" <?= $lecturer['designation'] === 'Lecturer' ? 'selected' : '' ?>>Lecturer</option>
                                        <option value="Lecturer (Probationary)" <?= $lecturer['designation'] === 'Lecturer (Probationary)' ? 'selected' : '' ?>>Lecturer (Probationary)</option>
                                    </select>
                                    <?php if (isset($errors['designation'])): ?>
                                    <div class="invalid-feedback"><?= $errors['designation'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['department']) ? 'is-invalid' : '' ?>" 
                                           id="department" name="department" value="<?= htmlspecialchars($lecturer['department'] ?? '') ?>" 
                                           list="departmentList" required>
                                    <datalist id="departmentList">
                                        <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <?php if (isset($errors['department'])): ?>
                                    <div class="invalid-feedback"><?= $errors['department'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="faculty" class="form-label">Faculty <span class="text-danger">*</span></label>
                                    <select class="form-select <?= isset($errors['faculty']) ? 'is-invalid' : '' ?>" 
                                            id="faculty" name="faculty" required>
                                        <option value="">Select Faculty</option>
                                        <option value="SCI" <?= $lecturer['faculty'] === 'SCI' ? 'selected' : '' ?>>Science</option>
                                        <option value="ENG" <?= $lecturer['faculty'] === 'ENG' ? 'selected' : '' ?>>Engineering</option>
                                        <option value="ART" <?= $lecturer['faculty'] === 'ART' ? 'selected' : '' ?>>Arts</option>
                                        <option value="COM" <?= $lecturer['faculty'] === 'COM' ? 'selected' : '' ?>>Commerce</option>
                                        <option value="MED" <?= $lecturer['faculty'] === 'MED' ? 'selected' : '' ?>>Medicine</option>
                                    </select>
                                    <?php if (isset($errors['faculty'])): ?>
                                    <div class="invalid-feedback"><?= $errors['faculty'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rank" class="form-label">Rank <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?= isset($errors['rank']) ? 'is-invalid' : '' ?>" 
                                           id="rank" name="rank" value="<?= htmlspecialchars($lecturer['rank'] ?? '1') ?>" 
                                           min="1" max="100" required>
                                    <?php if (isset($errors['rank'])): ?>
                                    <div class="invalid-feedback"><?= $errors['rank'] ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Lower numbers indicate higher rank</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" value="<?= htmlspecialchars($lecturer['email'] ?? '') ?>">
                                    <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                           id="phone" name="phone" value="<?= htmlspecialchars($lecturer['phone'] ?? '') ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="availability" name="availability" 
                                   <?= $lecturer['availability'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="availability">Currently Available for Duties</label>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/lecturers" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Lecturer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus on first invalid field or name field
    const firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.focus();
    } else {
        document.getElementById('name')?.focus();
    }
});
</script>

<?php require_once '../partials/footer.php'; ?>