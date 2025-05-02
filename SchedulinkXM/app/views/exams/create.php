<?php require_once '../partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>Create New Exam
                        </h4>
                        <a href="../exams/list.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Exams
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form action="/exams/store" method="POST" id="examForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" 
                                           id="date" name="date" value="<?= htmlspecialchars($data['date'] ?? '') ?>" 
                                           min="<?= date('Y-m-d') ?>" required>
                                    <?php if (isset($errors['date'])): ?>
                                    <div class="invalid-feedback"><?= $errors['date'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($errors['start_time']) ? 'is-invalid' : '' ?>" 
                                           id="start_time" name="start_time" value="<?= htmlspecialchars($data['start_time'] ?? '09:00') ?>" required>
                                    <?php if (isset($errors['start_time'])): ?>
                                    <div class="invalid-feedback"><?= $errors['start_time'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>" 
                                           id="end_time" name="end_time" value="<?= htmlspecialchars($data['end_time'] ?? '11:00') ?>" required>
                                    <?php if (isset($errors['end_time'])): ?>
                                    <div class="invalid-feedback"><?= $errors['end_time'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" 
                                           id="subject" name="subject" value="<?= htmlspecialchars($data['subject'] ?? '') ?>" required>
                                    <?php if (isset($errors['subject'])): ?>
                                    <div class="invalid-feedback"><?= $errors['subject'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="degree" class="form-label">Degree Program <span class="text-danger">*</span></label>
                                    <select class="form-select <?= isset($errors['degree']) ? 'is-invalid' : '' ?>" 
                                            id="degree" name="degree" required>
                                        <option value="">Select Degree</option>
                                        <option value="BIO" <?= isset($data['degree']) && $data['degree'] === 'BIO' ? 'selected' : '' ?>>Biological Sciences</option>
                                        <option value="PHY" <?= isset($data['degree']) && $data['degree'] === 'PHY' ? 'selected' : '' ?>>Physics</option>
                                        <option value="ECO" <?= isset($data['degree']) && $data['degree'] === 'ECO' ? 'selected' : '' ?>>Economics</option>
                                        <option value="CSE" <?= isset($data['degree']) && $data['degree'] === 'CSE' ? 'selected' : '' ?>>Computer Science</option>
                                        <option value="ENG" <?= isset($data['degree']) && $data['degree'] === 'ENG' ? 'selected' : '' ?>>Engineering</option>
                                        <option value="ART" <?= isset($data['degree']) && $data['degree'] === 'ART' ? 'selected' : '' ?>>Arts</option>
                                    </select>
                                    <?php if (isset($errors['degree'])): ?>
                                    <div class="invalid-feedback"><?= $errors['degree'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="regular_students" class="form-label">Regular Students <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?= isset($errors['regular_students']) ? 'is-invalid' : '' ?>" 
                                           id="regular_students" name="regular_students" 
                                           value="<?= htmlspecialchars($data['regular_students'] ?? '0') ?>" min="0" required>
                                    <?php if (isset($errors['regular_students'])): ?>
                                    <div class="invalid-feedback"><?= $errors['regular_students'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="repeat_students" class="form-label">Repeat Students <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?= isset($errors['repeat_students']) ? 'is-invalid' : '' ?>" 
                                           id="repeat_students" name="repeat_students" 
                                           value="<?= htmlspecialchars($data['repeat_students'] ?? '0') ?>" min="0" required>
                                    <?php if (isset($errors['repeat_students'])): ?>
                                    <div class="invalid-feedback"><?= $errors['repeat_students'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Exam
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
    // Set minimum end time based on start time
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    startTimeInput.addEventListener('change', function() {
        endTimeInput.min = this.value;
        if (endTimeInput.value < this.value) {
            endTimeInput.value = this.value;
        }
    });
    
    // Focus on first invalid field or subject field
    const firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.focus();
    } else {
        document.getElementById('subject')?.focus();
    }
});
</script>

<?php require_once '../partials/footer.php'; ?>