<?php
class LecturerController extends Controller {
    public function index() {
        $lecturerModel = $this->model('LecturerModel');
        $lecturers = $lecturerModel->getAllLecturers();
        $this->view('lecturers/list', ['lecturers' => $lecturers]);
    }
    
    public function create() {
        $this->view('lecturers/create');
    }
    
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('/lecturers/create');
        }
        
        $data = [
            'name' => $_POST['name'],
            'designation' => $_POST['designation'],
            'department' => $_POST['department'],
            'rank' => (int)$_POST['rank'],
            'faculty' => $_POST['faculty'],
            'availability' => isset($_POST['availability']) ? 1 : 0
        ];
        
        $lecturerModel = $this->model('LecturerModel');
        if ($lecturerModel->createLecturer($data)) {
            $_SESSION['success'] = 'Lecturer added successfully';
            $this->redirect('/lecturers');
        } else {
            $_SESSION['error'] = 'Failed to add lecturer';
            $this->view('lecturers/create', ['data' => $data]);
        }
    }
}
?>