<?php
class ExamController extends Controller {
    public function index() {
        $examModel = $this->model('ExamModel');
        $exams = $examModel->getAllExams();
        $this->view('exams/list', ['exams' => $exams]);
    }
    
    public function create() {
        $this->view('exams/create');
    }
    
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('/exams/create');
        }
        
        $data = [
            'date' => $_POST['date'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'subject' => $_POST['subject'],
            'degree' => $_POST['degree'],
            'regular_students' => (int)$_POST['regular_students'],
            'repeat_students' => (int)$_POST['repeat_students']
        ];
        
        $examModel = $this->model('ExamModel');
        if ($examModel->createExam($data)) {
            $_SESSION['success'] = 'Exam created successfully';
            $this->redirect('/exams');
        } else {
            $_SESSION['error'] = 'Failed to create exam';
            $this->view('exams/create', ['data' => $data]);
        }
    }
    
    // Implement edit, update, delete similarly
    
}
?>