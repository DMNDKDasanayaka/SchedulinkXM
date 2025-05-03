<?php
class AllocationManager {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Automatically allocate staff to exam sessions based on university rules
     */
    public function autoAllocateExams() {
        // 1. Get all upcoming exams that need allocation
        $exams = $this->getUnallocatedExams();
        
        foreach ($exams as $exam) {
            // 2. Calculate required staff based on student count
            $requiredStaff = $this->calculateRequiredStaff($exam['total_students']);
            
            // 3. Get available lecturers for this exam session
            $availableStaff = $this->getAvailableStaff($exam);
            
            // 4. Allocate supervisor (following hierarchy rules)
            $allocationResult = $this->allocateSupervisor($exam, $availableStaff, $requiredStaff['supervisors']);
            
            if (!$allocationResult['success']) {
                $this->logAllocationIssue($exam, "Failed to allocate supervisor: ".$allocationResult['message']);
                continue;
            }
            
            // 5. Allocate invigilators (must be lower rank than supervisor)
            $invigilatorResult = $this->allocateInvigilators(
                $exam, 
                $availableStaff, 
                $requiredStaff['invigilators'],
                $allocationResult['supervisor_rank']
            );
            
            if (!$invigilatorResult['success']) {
                $this->logAllocationIssue($exam, "Failed to allocate invigilators: ".$invigilatorResult['message']);
                continue;
            }
            
            // 6. Allocate hall attendants
            $attendantResult = $this->allocateHallAttendants(
                $exam,
                $availableStaff,
                $requiredStaff['hall_attendants']
            );
            
            // 7. Save the allocation to database
            $this->saveAllocation(
                $exam['exam_id'],
                $allocationResult['supervisor_id'],
                $invigilatorResult['invigilator_ids'],
                $attendantResult['attendant_ids'],
                $exam['hall_id']
            );
        }
    }
    
    /**
     * Calculate required staff based on student count
     */
    private function calculateRequiredStaff($studentCount) {
        $required = [
            'supervisors' => ceil($studentCount / 180),
            'invigilators' => max(1, ceil(($studentCount - 25) / 15) + 1),
            'hall_attendants' => ceil($studentCount / 180)
        ];
        
        return $required;
    }
    
    /**
     * Get available staff for an exam session
     */
    private function getAvailableStaff($exam) {
        $query = "SELECT l.*, r.rank_value 
                  FROM lecturers l
                  JOIN lecturer_ranks r ON l.rank_id = r.rank_id
                  WHERE l.faculty_id = ?
                  AND l.lecturer_id NOT IN (
                      SELECT a.lecturer_id 
                      FROM allocations a
                      JOIN exams e ON a.exam_id = e.exam_id
                      WHERE e.date = ?
                      AND (
                          (e.start_time < ? AND e.end_time > ?) OR
                          (e.start_time < ? AND e.end_time > ?) OR
                          (e.start_time >= ? AND e.end_time <= ?)
                      )
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssssss", 
            $exam['faculty_id'],
            $exam['date'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $exam['start_time'], $exam['end_time']
        );
        $stmt->execute();
        $result = $stmt->get_result();
        
        $staff = [];
        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
        
        return $staff;
    }
    
    /**
     * Allocate supervisor following hierarchy rules
     */
    private function allocateSupervisor($exam, $availableStaff, $requiredSupervisors) {
        // Check if paper setter is available and eligible
        $setter = $this->getPaperSetter($exam['subject_code']);
        
        if ($setter && !$this->isTrainingLecturer($setter) && $this->isAvailable($setter, $exam)) {
            return [
                'success' => true,
                'supervisor_id' => $setter['lecturer_id'],
                'supervisor_rank' => $setter['rank_value']
            ];
        }
        
        // If setter not available, find highest rank available lecturer
        usort($availableStaff, function($a, $b) {
            return $b['rank_value'] - $a['rank_value'];
        });
        
        if (count($availableStaff) {
            return [
                'success' => true,
                'supervisor_id' => $availableStaff[0]['lecturer_id'],
                'supervisor_rank' => $availableStaff[0]['rank_value']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'No available lecturers for supervisor role'
        ];
    }
    
    /**
     * Allocate invigilators who are lower rank than supervisor
     */
    private function allocateInvigilators($exam, $availableStaff, $requiredInvigilators, $supervisorRank) {
        // Filter out staff who are higher/equal rank to supervisor
        $eligibleInvigilators = array_filter($availableStaff, function($staff) use ($supervisorRank) {
            return $staff['rank_value'] < $supervisorRank;
        });
        
        // Sort by rank (lower ranks first to spread workload)
        usort($eligibleInvigilators, function($a, $b) {
            return $a['rank_value'] - $b['rank_value'];
        });
        
        $allocated = [];
        $needed = $requiredInvigilators;
        
        foreach ($eligibleInvigilators as $staff) {
            if ($needed <= 0) break;
            
            // Ensure not already allocated as supervisor
            if (!in_array($staff['lecturer_id'], $allocated)) {
                $allocated[] = $staff['lecturer_id'];
                $needed--;
            }
        }
        
        if (count($allocated) < $requiredInvigilators) {
            return [
                'success' => false,
                'message' => 'Not enough eligible invigilators available'
            ];
        }
        
        return [
            'success' => true,
            'invigilator_ids' => $allocated
        ];
    }
    
    /**
     * Allocate hall attendants
     */
    private function allocateHallAttendants($exam, $availableStaff, $requiredAttendants) {
        // Hall attendants can be any available staff
        $allocated = [];
        $needed = $requiredAttendants;
        
        foreach ($availableStaff as $staff) {
            if ($needed <= 0) break;
            
            if (!in_array($staff['lecturer_id'], $allocated)) {
                $allocated[] = $staff['lecturer_id'];
                $needed--;
            }
        }
        
        return [
            'success' => count($allocated) >= $requiredAttendants,
            'attendant_ids' => $allocated
        ];
    }
    
    /**
     * Save allocation to database
     */
    private function saveAllocation($examId, $supervisorId, $invigilatorIds, $attendantIds, $hallId) {
        try {
            // Begin transaction
            $this->conn->begin_transaction();
            
            // Insert supervisor allocation
            $supervisorQuery = "INSERT INTO allocations 
                               (exam_id, lecturer_id, hall_id, role, assigned_at) 
                               VALUES (?, ?, ?, 'supervisor', NOW())";
            $stmt = $this->conn->prepare($supervisorQuery);
            $stmt->bind_param("iii", $examId, $supervisorId, $hallId);
            $stmt->execute();
            
            // Insert invigilator allocations
            $invigilatorQuery = "INSERT INTO allocations 
                                 (exam_id, lecturer_id, hall_id, role, assigned_at) 
                                 VALUES (?, ?, ?, 'invigilator', NOW())";
            $stmt = $this->conn->prepare($invigilatorQuery);
            
            foreach ($invigilatorIds as $invigilatorId) {
                $stmt->bind_param("iii", $examId, $invigilatorId, $hallId);
                $stmt->execute();
            }
            
            // Insert hall attendant allocations
            $attendantQuery = "INSERT INTO allocations 
                              (exam_id, lecturer_id, hall_id, role, assigned_at) 
                              VALUES (?, ?, ?, 'hall_attendant', NOW())";
            $stmt = $this->conn->prepare($attendantQuery);
            
            foreach ($attendantIds as $attendantId) {
                $stmt->bind_param("iii", $examId, $attendantId, $hallId);
                $stmt->execute();
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Allocation save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Helper function to get paper setter for a subject
     */
    private function getPaperSetter($subjectCode) {
        $query = "SELECT l.*, r.rank_value 
                  FROM lecturers l
                  JOIN lecturer_ranks r ON l.rank_id = r.rank_id
                  JOIN subject_setters s ON l.lecturer_id = s.lecturer_id
                  WHERE s.subject_code = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $subjectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Check if a lecturer is a Training Lecturer
     */
    private function isTrainingLecturer($lecturer) {
        // Assuming there's a flag in the lecturers table
        return $lecturer['is_training'] == 1;
    }
    
    /**
     * Check if a lecturer is available for an exam session
     */
    private function isAvailable($lecturer, $exam) {
        $query = "SELECT COUNT(*) as conflict 
                  FROM allocations a
                  JOIN exams e ON a.exam_id = e.exam_id
                  WHERE a.lecturer_id = ?
                  AND e.date = ?
                  AND (
                      (e.start_time < ? AND e.end_time > ?) OR
                      (e.start_time < ? AND e.end_time > ?) OR
                      (e.start_time >= ? AND e.end_time <= ?)
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssssss", 
            $lecturer['lecturer_id'],
            $exam['date'],
            $exam['end_time'], $exam['start_time'],
            $exam['start_time'], $exam['end_time'],
            $exam['start_time'], $exam['end_time']
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['conflict'] == 0;
    }
    
    /**
     * Get exams that need allocation
     */
    private function getUnallocatedExams() {
        $query = "SELECT e.*, h.hall_id, f.faculty_id, 
                         (e.enrolled_students + e.repeat_students) as total_students
                  FROM exams e
                  JOIN exam_halls h ON e.primary_hall_id = h.hall_id
                  JOIN faculties f ON e.faculty_id = f.faculty_id
                  WHERE e.date >= CURDATE()
                  AND NOT EXISTS (
                      SELECT 1 FROM allocations a WHERE a.exam_id = e.exam_id
                  )";
        
        $result = $this->conn->query($query);
        $exams = [];
        
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        
        return $exams;
    }
    
    /**
     * Log allocation issues for admin review
     */
    private function logAllocationIssue($exam, $message) {
        $query = "INSERT INTO allocation_issues 
                  (exam_id, issue_message, logged_at) 
                  VALUES (?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $exam['exam_id'], $message);
        $stmt->execute();
    }
    
    /**
     * Validate rank hierarchy for an allocation
     */
    public function validateRankHierarchy($supervisorRank, $invigilatorRanks) {
        foreach ($invigilatorRanks as $rank) {
            if ($rank >= $supervisorRank) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get rank hierarchy info for an allocation
     */
    public function getRankHierarchyInfo($allocationId) {
        $query = "SELECT l.name, l.lecturer_id, r.rank_name, r.rank_value, a.role
                  FROM allocations a
                  JOIN lecturers l ON a.lecturer_id = l.lecturer_id
                  JOIN lecturer_ranks r ON l.rank_id = r.rank_id
                  WHERE a.exam_id = (
                      SELECT exam_id FROM allocations WHERE allocation_id = ?
                  )
                  AND a.hall_id = (
                      SELECT hall_id FROM allocations WHERE allocation_id = ?
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $allocationId, $allocationId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hierarchy = [
            'supervisor' => null,
            'invigilators' => []
        ];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['role'] === 'supervisor') {
                $hierarchy['supervisor'] = [
                    'name' => $row['name'],
                    'rank' => $row['rank_name'],
                    'value' => $row['rank_value']
                ];
            } else if ($row['role'] === 'invigilator') {
                $hierarchy['invigilators'][] = [
                    'name' => $row['name'],
                    'rank' => $row['rank_name'],
                    'value' => $row['rank_value']
                ];
            }
        }
        
        return $hierarchy;
    }
}
?>