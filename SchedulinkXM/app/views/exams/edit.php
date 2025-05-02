<?php require_once '../partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-edit me-2"></i>Edit Exam
                        </h4>
                        <a href="/exams" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Exams
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form action="/exams/update/<?= $exam['id'] ?>" method="POST" id="examForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" 
                                           id="date" name="date" value="<?= htmlspecialchars($exam['date'] ?? '') ?>" required>
                                    <?php if (isset($errors['date'])): ?>
                                    <div class="invalid-feedback"><?= $errors['date'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($errors['start_time']) ? 'is-invalid' : '' ?>" 
                                           id="start_time" name="start_time" value="<?= htmlspecialchars($exam['start_time'] ?? '') ?>" required>
                                    <?php if (isset($errors['start_time'])): ?>
                                    <div class="invalid-feedback"><?= $errors['start_time'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>" 
                                           id="end_time" name="end_time" value="<?= htmlspecialchars($exam['end_time'] ?? '') ?>" required>
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
                                           id="subject" name="subject" value="<?= htmlspecialchars($exam['subject'] ?? '') ?>" required>
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
                                        <option value="BIO" <?= $exam['degree'] === 'BIO' ? 'selected' : '' ?>>Biological Sciences</option>
                                        <option value="PHY" <?= $exam['degree'] === 'PHY' ? 'selected' : '' ?>>Physics</option>
                                        <option value="ECO" <?= $exam['degree'] === 'ECO' ? 'selected' : '' ?>>Economics</option>
                                        <option value="CSE" <?= $exam['degree'] === 'CSE' ? 'selected' : '' ?>>Computer Science</option>
                                        <option value="ENG" <?= $exam['degree'] === 'ENG' ? 'selected' : '' ?>>Engineering</option>
                                        <option value="ART" <?= $exam['degree'] === 'ART' ? 'selected' : '' ?>>Arts</option>
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
                                           value="<?= htmlspecialchars($exam['regular_students'] ?? '0') ?>" min="0" required>
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
                                           value="<?= htmlspecialchars($exam['repeat_students'] ?? '0') ?>" min="0" required>
                                    <?php if (isset($errors['repeat_students'])): ?>
                                    <div class="invalid-feedback"><?= $errors['repeat_students'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($exam['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/exams" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Exam
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