<?php
class AuthController extends Controller {
    public function login() {
        if ($this->isPost()) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $userModel = $this->model('UserModel');
            $user = $userModel->authenticate($username, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $this->redirect('/dashboard');
            } else {
                $this->view('auth/login', ['error' => 'Invalid credentials']);
            }
        } else {
            $this->view('auth/login');
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
    
    // Add register method if needed
}
?>