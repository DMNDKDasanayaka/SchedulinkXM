<?php
class ConflictDetector {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function checkTimeConflicts($lecturerId, $examDate, $startTime, $endTime) {
        $query = "SELECT e.exam_id, e.subject_name 
                 FROM allocations a
                 JOIN exams e ON a.exam_id = e.exam_id
                 WHERE (a.supervisor_id = ? OR JSON_CONTAINS(a.invigilator_ids, CAST(? AS JSON)))
                 AND e.date = ?
                 AND ((e.start_time < ? AND e.end_time > ?)
                 OR (e.start_time BETWEEN ? AND ?)
                 OR (e.end_time BETWEEN ? AND ?))";
        
        $stmt = $this->conn->prepare($query);
        $lecturerIdJson = json_encode($lecturerId);
        $stmt->bind_param("isssssss", $lecturerId, $lecturerIdJson, $examDate,
                         $endTime, $startTime, $startTime, $endTime, $startTime, $endTime);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function validateRankHierarchy($supervisorId, $invigilatorIds) {
        $supervisorRank = $this->getLecturerRank($supervisorId);
        
        foreach ($invigilatorIds as $invigilatorId) {
            $invigilatorRank = $this->getLecturerRank($invigilatorId);
            if ($invigilatorRank <= $supervisorRank) {
                return false;
            }
        }
        return true;
    }

    public function isTrainingLecturer($lecturerId) {
        $query = "SELECT is_training_lecturer FROM lecturers WHERE lecturer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $lecturerId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['is_training_lecturer'] == 1;
    }

    private function getLecturerRank($lecturerId) {
        $query = "SELECT rank_level FROM lecturers WHERE lecturer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $lecturerId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['rank_level'];
    }
}
?>