<?php
class HallController extends Controller {
    public function index() {
        // Check if user is logged in and has permission
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login to access this page';
            $this->redirect('/login');
        }

        $hallModel = $this->model('HallModel');
        $halls = $hallModel->getAllHalls();
        
        $this->view('halls/list', [
            'halls' => $halls,
            'canEdit' => $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'
        ]);
    }

    public function create() {
        // Authorization check
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
            $_SESSION['error'] = 'You do not have permission to perform this action';
            $this->redirect('/halls');
        }

        $this->view('halls/create');
    }

    public function store() {
        // Authorization check
        if (!$this->isPost() || !isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
            $_SESSION['error'] = 'Invalid request';
            $this->redirect('/halls');
        }

        // Validate input
        $errors = [];
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'capacity' => trim($_POST['capacity'] ?? ''),
            'faculty' => trim($_POST['faculty'] ?? '')
        ];

        if (empty($data['name'])) {
            $errors['name'] = 'Hall name is required';
        }

        if (empty($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] <= 0) {
            $errors['capacity'] = 'Valid capacity is required';
        } else {
            $data['capacity'] = (int)$data['capacity'];
        }

        if (!empty($errors)) {
            $this->view('halls/create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        // Save to database
        $hallModel = $this->model('HallModel');
        if ($hallModel->createHall($data)) {
            $_SESSION['success'] = 'Hall created successfully';
            $this->redirect('/halls');
        } else {
            $_SESSION['error'] = 'Failed to create hall';
            $this->view('halls/create', ['data' => $data]);
        }
    }

    public function edit($id) {
        // Authorization check
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
            $_SESSION['error'] = 'You do not have permission to perform this action';
            $this->redirect('/halls');
        }

        $hallModel = $this->model('HallModel');
        $hall = $hallModel->getHallById($id);

        if (!$hall) {
            $_SESSION['error'] = 'Hall not found';
            $this->redirect('/halls');
        }

        $this->view('halls/edit', ['hall' => $hall]);
    }

    public function update($id) {
        // Authorization check
        if (!$this->isPost() || !isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
            $_SESSION['error'] = 'Invalid request';
            $this->redirect('/halls');
        }

        $hallModel = $this->model('HallModel');
        $hall = $hallModel->getHallById($id);

        if (!$hall) {
            $_SESSION['error'] = 'Hall not found';
            $this->redirect('/halls');
        }

        // Validate input
        $errors = [];
        $data = [
            'id' => $id,
            'name' => trim($_POST['name'] ?? ''),
            'capacity' => trim($_POST['capacity'] ?? ''),
            'faculty' => trim($_POST['faculty'] ?? '')
        ];

        if (empty($data['name'])) {
            $errors['name'] = 'Hall name is required';
        }

        if (empty($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] <= 0) {
            $errors['capacity'] = 'Valid capacity is required';
        } else {
            $data['capacity'] = (int)$data['capacity'];
        }

        if (!empty($errors)) {
            $this->view('halls/edit', [
                'errors' => $errors,
                'hall' => $data
            ]);
            return;
        }

        // Update database
        if ($hallModel->updateHall($data)) {
            $_SESSION['success'] = 'Hall updated successfully';
            $this->redirect('/halls');
        } else {
            $_SESSION['error'] = 'Failed to update hall';
            $this->view('halls/edit', ['hall' => $data]);
        }
    }

    public function delete($id) {
        // Authorization check
        if (!$this->isPost() || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to perform this action';
            $this->redirect('/halls');
        }

        $hallModel = $this->model('HallModel');
        $hall = $hallModel->getHallById($id);

        if (!$hall) {
            $_SESSION['error'] = 'Hall not found';
            $this->redirect('/halls');
        }

        // Check if hall is assigned to any exams
        $examModel = $this->model('ExamModel');
        if ($examModel->isHallAssigned($id)) {
            $_SESSION['error'] = 'Cannot delete hall assigned to exams';
            $this->redirect('/halls');
        }

        if ($hallModel->deleteHall($id)) {
            $_SESSION['success'] = 'Hall deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete hall';
        }

        $this->redirect('/halls');
    }

    public function availability($id) {
        // Check if hall is available at given date/time (for AJAX requests)
        if (!$this->isPost()) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit();
        }

        $date = $_POST['date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';

        if (empty($date) || empty($startTime) || empty($endTime)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            exit();
        }

        $hallModel = $this->model('HallModel');
        $isAvailable = $hallModel->checkAvailability($id, $date, $startTime, $endTime);

        header('Content-Type: application/json');
        echo json_encode(['available' => $isAvailable]);
        exit();
    }
}
?>