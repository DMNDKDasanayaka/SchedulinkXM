<?php 
//require_once '../partials/header.php';
?>

<style>
/* Color Variables */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --dark-color: #2c3e50;
    --light-color: #f8f9fa;
    --accent-color: #6a11cb;
    --success-color: #38a169;
    --error-color: #e53e3e;
    --text-dark: #2d3748;
    --text-light: #f7fafc;
}

/* Base Styles */
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--light-color);
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    position: relative;
    overflow: hidden;
    padding: 2rem;
}

.login-page::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(106,17,203,0.08) 0%, rgba(255,255,255,0) 70%);
    z-index: 0;
}

/* Login Container */
.login-container {
    max-width: 480px;
    width: 100%;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Login Card */
.login-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: white;
    position: relative;
}

.login-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1);
}

.login-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: var(--primary-gradient);
}

/* Header Styles */
.login-header {
    background: white;
    color: var(--text-dark);
    padding: 2rem 2rem 1rem;
    text-align: center;
    position: relative;
}

.login-header h2 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--accent-color);
    font-size: 1.8rem;
    position: relative;
    padding-bottom: 0.5rem;
}

.login-header h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: var(--primary-gradient);
    border-radius: 3px;
}

.login-header .system-subtitle {
    font-size: 0.95rem;
    color: #718096;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}

.login-header i {
    font-size: 1.8rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Form Elements */
.login-form {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.75rem;
    position: relative;
}

.form-label {
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    font-size: 1rem;
    background-color: #f8fafc;
    color: var(--text-dark);
}

.form-control:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
    outline: none;
    background-color: white;
}

.input-group {
    position: relative;
    display: flex;
    align-items: stretch;
    width: 100%;
}

.input-group-text {
    background-color: #f8fafc;
    border: 2px solid #e2e8f0;
    border-right: none;
    border-radius: 10px 0 0 10px;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    transition: all 0.3s;
}

.input-group input {
    border-left: none;
    border-radius: 0 10px 10px 0 !important;
}

.input-group:focus-within .input-group-text {
    color: var(--accent-color);
    border-color: var(--accent-color);
    background-color: white;
}

/* Button Styles */
.btn-login {
    background: var(--primary-gradient);
    border: none;
    border-radius: 10px;
    padding: 1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    color: white;
    width: 100%;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--secondary-gradient);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: -1;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(106, 17, 203, 0.2);
}

.btn-login:hover::before {
    opacity: 1;
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login i {
    transition: transform 0.3s ease;
}

.btn-login:hover i {
    transform: translateX(3px);
}

/* Toggle Password Button */
.toggle-password {
    background-color: #f8fafc;
    border: 2px solid #e2e8f0;
    border-left: none;
    border-radius: 0 10px 10px 0;
    cursor: pointer;
    transition: all 0.3s;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    color: #718096;
}

.toggle-password:hover {
    background-color: #edf2f7;
    color: var(--accent-color);
}

/* Remember Me Checkbox */
.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 1.75rem;
    position: relative;
}

.form-check-input {
    width: 20px;
    height: 20px;
    margin: 0;
    appearance: none;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    background-color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-check-input:checked {
    background: var(--accent-color);
    border-color: var(--accent-color);
}

.form-check-input:checked::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    color: white;
    font-size: 0.8rem;
}

.form-check-label {
    font-size: 0.95rem;
    color: var(--text-dark);
    cursor: pointer;
    user-select: none;
}

/* Footer Links */
.login-footer {
    background-color: #f8fafc;
    padding: 1.25rem;
    text-align: center;
    border-top: 1px solid #e2e8f0;
    font-size: 0.9rem;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.login-footer a {
    color: #718096;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.25rem 0;
}

.login-footer a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--accent-color);
    transition: width 0.3s ease;
}

.login-footer a:hover {
    color: var(--accent-color);
}

.login-footer a:hover::after {
    width: 100%;
}

/* University Branding */
.university-branding {
    text-align: center;
    margin-top: 2.5rem;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.university-branding:hover {
    opacity: 1;
}

.university-logo {
    height: 70px;
    margin-bottom: 1rem;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.1));
    transition: transform 0.3s ease;
}

.university-branding:hover .university-logo {
    transform: scale(1.05);
}

.system-version {
    font-size: 0.85rem;
    color: #a0aec0;
    margin-top: 0.75rem;
    letter-spacing: 0.5px;
}

/* Error States */
.is-invalid {
    border-color: var(--error-color) !important;
}

.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1) !important;
}

.invalid-feedback {
    color: var(--error-color);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: block;
    font-weight: 500;
}

/* Alert Messages */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.75rem;
    border-left: 4px solid transparent;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-danger {
    color: var(--error-color);
    background-color: rgba(229, 62, 62, 0.1);
    border-left-color: var(--error-color);
}

.alert i {
    font-size: 1.2rem;
}

/* Floating Decorations */
.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
}

.floating-element {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(106,17,203,0.1) 0%, rgba(255,255,255,0) 100%);
    animation: float 15s infinite linear;
}

.floating-element:nth-child(1) {
    width: 150px;
    height: 150px;
    top: 10%;
    left: 5%;
    animation-duration: 20s;
}

.floating-element:nth-child(2) {
    width: 200px;
    height: 200px;
    bottom: 15%;
    right: 5%;
    animation-duration: 25s;
    animation-direction: reverse;
}

@keyframes float {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
    100% {
        transform: translateY(0) rotate(360deg);
    }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .login-page {
        padding: 1.5rem;
    }
    
    .login-header h2 {
        font-size: 1.6rem;
    }
    
    .login-form {
        padding: 1.75rem;
    }
    
    .floating-elements {
        display: none;
    }
}

@media (max-width: 480px) {
    .login-page {
        padding: 1rem;
    }
    
    .login-header {
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .login-form {
        padding: 1.5rem;
    }
    
    .login-footer {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<div class="login-page">
    <!-- Floating background elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>
                    <i class="fas fa-lock"></i>
                    SchedulinkXM
                </h2>
                <div class="system-subtitle">Linking Duties, Halls, and Exams Seamlessly</div>
            </div>
            
            <div class="login-form">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="/SchedulinkXM/SchedulinkXM/app/handlers/login.php" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                   required autofocus>
                        </div>
                        <?php if (isset($errors['username'])): ?>
                        <span class="invalid-feedback">
                            <i class="fas fa-exclamation-circle"></i> <?= $errors['username'] ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" required>
                            <button type="button" class="toggle-password" 
                                    data-target="#password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                        <span class="invalid-feedback">
                            <i class="fas fa-exclamation-circle"></i> <?= $errors['password'] ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <a href="/forgot-password"><i class="fas fa-key"></i> Forgot password?</a>
                <a href="/contact"><i class="fas fa-headset"></i> Contact administrator</a>
            </div>
        </div>
        
        
    </div>
</div>

<script>
// Enhanced toggle password visibility with animation
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const target = document.querySelector(this.dataset.target);
        const icon = this.querySelector('i');
        
        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            this.style.color = 'var(--accent-color)';
            
            // Add temporary animation
            this.style.transform = 'scale(1.2)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
        } else {
            target.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            this.style.color = '';
        }
    });
});

// Focus on first invalid field or username by default with animation
document.addEventListener('DOMContentLoaded', () => {
    const invalidInput = document.querySelector('.is-invalid');
    if (invalidInput) {
        invalidInput.focus();
        invalidInput.style.animation = 'pulse 1s 1';
        setTimeout(() => {
            invalidInput.style.animation = '';
        }, 1000);
    } else {
        const usernameField = document.getElementById('username');
        if (usernameField) {
            usernameField.focus();
        }
    }
});

// Add pulse animation for errors
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(229, 62, 62, 0); }
        100% { box-shadow: 0 0 0 0 rgba(229, 62, 62, 0); }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once '../partials/footer.php'; ?>