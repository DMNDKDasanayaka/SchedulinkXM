<?php
class ExamModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllExams() {
        $stmt = $this->db->query("SELECT * FROM exams ORDER BY date, start_time");
        return $stmt->fetchAll();
    }
    
    public function createExam($data) {
        $stmt = $this->db->prepare("INSERT INTO exams 
            (date, start_time, end_time, subject, degree, regular_students, repeat_students) 
            VALUES (:date, :start_time, :end_time, :subject, :degree, :regular_students, :repeat_students)");
        
        return $stmt->execute($data);
    }
    
    public function getExamById($id) {
        $stmt = $this->db->prepare("SELECT * FROM exams WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Add update and delete methods
}
?>