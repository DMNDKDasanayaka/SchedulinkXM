<?php
class LecturerModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllLecturers() {
        $stmt = $this->db->query("SELECT * FROM lecturers ORDER BY rank DESC, name ASC");
        return $stmt->fetchAll();
    }
    
    public function createLecturer($data) {
        $stmt = $this->db->prepare("INSERT INTO lecturers 
            (name, designation, department, rank, faculty, availability) 
            VALUES (:name, :designation, :department, :rank, :faculty, :availability)");
        
        return $stmt->execute($data);
    }
    
    public function getAvailableLecturers($date, $time) {
        // Implement logic to find lecturers available at given date/time
        // This would check against exam assignments
        $stmt = $this->db->query("SELECT * FROM lecturers WHERE availability = 1 ORDER BY rank DESC");
        return $stmt->fetchAll();
    }
}
?>