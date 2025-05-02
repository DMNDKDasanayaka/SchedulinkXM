<?php
class Controller {
    protected $db;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }
    
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model($this->db);
    }
    
    public function view($view, $data = []) {
        // Extract data to variables for the view
        extract($data);
        
        // Include header
        require_once '../app/views/partials/header.php';
        
        // Load the main view content
        require_once '../app/views/' . $view . '.php';
        
        // Include footer
        require_once '../app/views/partials/footer.php';
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}
?>