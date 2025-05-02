<?php
require_once '../includes/db_connect.php';

class AllocationManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Main allocation function
     */
    public function allocateDuties() {
        // Get all upcoming exams that need allocation
        $exams = $this->getUnallocatedExams();
        
        foreach ($exams as $exam) {
            $this->allocateExam($exam);
        }
    }
    
    /**
     * Allocate duties for a single exam
     */
    private function allocateExam($exam) {
        // 1. Calculate required staff based on student count
        $totalStudents = $exam['student_count'] + $exam['repeaters'];
        $supervisorsNeeded = ceil($totalStudents / 180);
        $invigilatorsNeeded = $this->calculateInvigilators($totalStudents);
        $attendantsNeeded = ceil($totalStudents / 180);
        
        // 2. Allocate halls
        $hallIds = $this->allocateHalls($exam, $totalStudents);
        if (empty($hallIds)) {
            $this->logError("No available halls for exam {$exam['exam_id']}");
            return false;
        }
        
        // 3. Allocate supervisors (following hierarchy rules)
        $supervisors = $this->allocateSupervisors($exam, $supervisorsNeeded);
        if (count($supervisors) < $supervisorsNeeded) {
            $this->logError("Insufficient supervisors for exam {$exam['exam_id']}");
            return false;
        }
        
        // 4. Allocate invigilators (must be lower rank than supervisor)
        $invigilators = $this->allocateInvigilators($exam, $invigilatorsNeeded, $supervisors);
        if (count($invigilators) < $invigilatorsNeeded) {
            $this->logError("Insufficient invigilators for exam {$exam['exam_id']}");
            return false;
        }
        
        // 5. Allocate hall attendants
        $attendants = $this->allocateAttendants($exam, $attendantsNeeded);
        if (count($attendants) < $attendantsNeeded) {
            $this->logError("Insufficient hall attendants for exam {$exam['exam_id']}");
            return false;
        }
        
        // 6. Create allocation records
        $this->createAllocations($exam, $hallIds, $supervisors, $invigilators, $attendants);
        
        return true;
    }
    
    /**
     * Calculate required invigilators based on student count
     */
    private function calculateInvigilators($studentCount) {
        if ($studentCount <= 25) return 1;
        return ceil(($studentCount - 25) / 15) + 1;
    }
    
    /**
     * Allocate halls for an exam
     */
    private function allocateHalls($exam, $totalStudents) {
        // Find available halls during exam time with enough capacity
        $query = "SELECT hall_id, capacity FROM exam_halls 
                 WHERE hall_id NOT IN (
                     SELECT hall_id FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date = ? AND (
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time >= ? AND e.end_time <= ?)
                     )
                 ORDER BY capacity ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", 
            $exam['date'], $exam['end_time'], $exam['start_time'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time']
        );
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hallIds = [];
        $remainingStudents = $totalStudents;
        
        while ($row = $result->fetch_assoc() && $remainingStudents > 0) {
            $hallIds[] = $row['hall_id'];
            $remainingStudents -= $row['capacity'];
        }
        
        return $remainingStudents <= 0 ? $hallIds : [];
    }
    
    /**
     * Allocate supervisors for an exam
     */
    private function allocateSupervisors($exam, $needed) {
        $supervisors = [];
        
        // 1. Try to use paper setter as supervisor (if available and not TL)
        if ($exam['paper_setter_id']) {
            $setter = $this->getLecturer($exam['paper_setter_id']);
            if ($setter && !$setter['is_training_lecturer'] && $this->isAvailable($setter, $exam)) {
                $supervisors[] = $setter;
            }
        }
        
        // 2. Find additional supervisors if needed
        if (count($supervisors) < $needed) {
            $additionalNeeded = $needed - count($supervisors);
            $query = "SELECT * FROM lecturers 
                     WHERE faculty = ? 
                     AND is_training_lecturer = FALSE
                     AND lecturer_id NOT IN (
                         SELECT supervisor_id FROM allocations a
                         JOIN exams e ON a.exam_id = e.exam_id
                         WHERE e.date = ? AND (
                             (e.start_time < ? AND e.end_time > ?) OR
                             (e.start_time < ? AND e.end_time > ?) OR
                             (e.start_time >= ? AND e.end_time <= ?)
                         )
                     )
                     ORDER BY rank_level ASC
                     LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssssssi", 
                $exam['degree'], $exam['date'], 
                $exam['end_time'], $exam['start_time'],
                $exam['end_time'], $exam['start_time'],
                $exam['start_time'], $exam['end_time'],
                $additionalNeeded
            );
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $supervisors[] = $row;
            }
        }
        
        return $supervisors;
    }
    
    /**
     * Allocate invigilators for an exam
     */
    private function allocateInvigilators($exam, $needed, $supervisors) {
        if (empty($supervisors)) return [];
        
        // Get minimum supervisor rank (invigilators must be lower rank)
        $minSupervisorRank = min(array_column($supervisors, 'rank_level'));
        
        $query = "SELECT * FROM lecturers 
                 WHERE faculty = ? 
                 AND rank_level > ?
                 AND lecturer_id NOT IN (
                     SELECT supervisor_id FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date = ? AND (
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time >= ? AND e.end_time <= ?)
                     )
                 )
                 AND lecturer_id NOT IN (
                     SELECT lecturer_id FROM allocation_invigilators ai
                     JOIN allocations a ON ai.allocation_id = a.allocation_id
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date = ? AND (
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time >= ? AND e.end_time <= ?)
                     )
                 )
                 ORDER BY rank_level ASC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sissssssssssssi", 
            $exam['degree'], $minSupervisorRank,
            $exam['date'], 
            $exam['end_time'], $exam['start_time'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $exam['date'],
            $exam['end_time'], $exam['start_time'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $needed
        );
        $stmt->execute();
        $result = $stmt->get_result();
        
        $invigilators = [];
        while ($row = $result->fetch_assoc()) {
            $invigilators[] = $row;
        }
        
        return $invigilators;
    }
    
    /**
     * Allocate hall attendants
     */
    private function allocateAttendants($exam, $needed) {
        $query = "SELECT * FROM lecturers 
                 WHERE faculty = ? 
                 AND lecturer_id NOT IN (
                     SELECT supervisor_id FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date = ? AND (
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time >= ? AND e.end_time <= ?)
                     )
                 )
                 AND lecturer_id NOT IN (
                     SELECT lecturer_id FROM allocation_attendants aa
                     JOIN allocations a ON aa.allocation_id = a.allocation_id
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date = ? AND (
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time < ? AND e.end_time > ?) OR
                         (e.start_time >= ? AND e.end_time <= ?)
                     )
                 )
                 ORDER BY rank_level ASC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssssssssssi", 
            $exam['degree'],
            $exam['date'], 
            $exam['end_time'], $exam['start_time'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $exam['date'],
            $exam['end_time'], $exam['start_time'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $needed
        );
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendants = [];
        while ($row = $result->fetch_assoc()) {
            $attendants[] = $row;
        }
        
        return $attendants;
    }
    
    /**
     * Create allocation records in database
     */
    private function createAllocations($exam, $hallIds, $supervisors, $invigilators, $attendants) {
        // Distribute supervisors and invigilators across halls
        $supervisorsPerHall = ceil(count($supervisors) / count($hallIds));
        $invigilatorsPerHall = ceil(count($invigilators) / count($hallIds));
        $attendantsPerHall = ceil(count($attendants) / count($hallIds));
        
        $supervisorIndex = 0;
        $invigilatorIndex = 0;
        $attendantIndex = 0;
        
        foreach ($hallIds as $hallId) {
            // Get supervisors for this hall
            $hallSupervisors = array_slice($supervisors, $supervisorIndex, $supervisorsPerHall);
            $supervisorIndex += $supervisorsPerHall;
            
            // Get invigilators for this hall
            $hallInvigilators = array_slice($invigilators, $invigilatorIndex, $invigilatorsPerHall);
            $invigilatorIndex += $invigilatorsPerHall;
            
            // Get attendants for this hall
            $hallAttendants = array_slice($attendants, $attendantIndex, $attendantsPerHall);
            $attendantIndex += $attendantsPerHall;
            
            if (empty($hallSupervisors)) continue;
            
            // Create allocation record
            $allocationQuery = "INSERT INTO allocations 
                               (exam_id, hall_id, supervisor_id) 
                               VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($allocationQuery);
            $stmt->bind_param("iii", $exam['exam_id'], $hallId, $hallSupervisors[0]['lecturer_id']);
            $stmt->execute();
            $allocationId = $this->db->insert_id;
            
            // Add invigilators
            foreach ($hallInvigilators as $invigilator) {
                $invigQuery = "INSERT INTO allocation_invigilators 
                              (allocation_id, lecturer_id) 
                              VALUES (?, ?)";
                $stmt = $this->db->prepare($invigQuery);
                $stmt->bind_param("ii", $allocationId, $invigilator['lecturer_id']);
                $stmt->execute();
            }
            
            // Add attendants
            foreach ($hallAttendants as $attendant) {
                $attQuery = "INSERT INTO allocation_attendants 
                            (allocation_id, lecturer_id) 
                            VALUES (?, ?)";
                $stmt = $this->db->prepare($attQuery);
                $stmt->bind_param("ii", $allocationId, $attendant['lecturer_id']);
                $stmt->execute();
            }
            
            // Log the allocation
            $this->logAllocation($allocationId, $_SESSION['user_id'], "Auto-allocated");
        }
    }
    
    // Helper methods...
    private function getUnallocatedExams() { /* ... */ }
    private function getLecturer($id) { /* ... */ }
    private function isAvailable($lecturer, $exam) { /* ... */ }
    private function logError($message) { /* ... */ }
    private function logAllocation($allocationId, $userId, $description) { /* ... */ }
}

// Usage example
$allocationManager = new AllocationManager($db);
$allocationManager->allocateDuties();
?>