<?php
class AllocationManager {
    private $conn;
    private $conflictDetector;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        require_once 'conflict_detection.php';
        $this->conflictDetector = new ConflictDetector($dbConnection);
    }

    public function autoAllocateExam($examId) {
        $this->conn->begin_transaction();
        try {
            $exam = $this->getExamDetails($examId);
            $requiredStaff = $this->calculateStaffRequirements($exam['student_count'], $exam['repeaters']);
            $hallAllocation = $this->allocateExamHalls($examId, $exam['date'], $exam['start_time'], $exam['end_time'], $requiredStaff['halls_needed']);
            
            $allocations = [];
            foreach ($hallAllocation['hall_ids'] as $hallId) {
                $allocation = $this->allocateHallStaff(
                    $examId, 
                    $hallId, 
                    $exam['faculty'], 
                    $requiredStaff
                );
                $allocations[] = $allocation;
            }
            
            $this->conn->commit();
            return ['success' => true, 'allocations' => $allocations];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Allocation failed: " . $e->getMessage());
            return ['error' => "Allocation failed. Please try again or contact admin."];
        }
    }

    private function calculateStaffRequirements($students, $repeaters) {
        $total = $students + $repeaters;
        return [
            'supervisors' => max(1, ceil($total / 180)),
            'invigilators' => max(1, ceil(($total - 25) / 15) + 1),
            'hall_attendants' => max(1, ceil($total / 180)),
            'halls_needed' => max(1, ceil($total / 150))
        ];
    }

    private function allocateHallStaff($examId, $hallId, $faculty, $requiredStaff) {
        // Allocate supervisor following hierarchy rules
        $supervisorId = $this->allocateSupervisor($examId, $faculty);
        if (!$supervisorId) {
            throw new Exception("No available supervisor found for faculty $faculty");
        }

        // Get supervisor rank for invigilator hierarchy
        $supervisorRank = $this->getLecturerRank($supervisorId);
        
        // Allocate invigilators with lower rank
        $invigilatorIds = $this->allocateInvigilators(
            $examId, 
            $hallId, 
            $requiredStaff['invigilators'], 
            $supervisorRank
        );
        
        // Allocate hall attendant
        $attendantId = $this->allocateHallAttendant($hallId);
        
        // Create allocation record
        $allocationId = $this->createAllocationRecord(
            $examId,
            $hallId,
            $supervisorId,
            $invigilatorIds
        );
        
        return [
            'allocation_id' => $allocationId,
            'supervisor_id' => $supervisorId,
            'invigilator_ids' => $invigilatorIds,
            'hall_attendant_id' => $attendantId
        ];
    }

    private function allocateSupervisor($examId, $faculty) {
        // 1. Try paper setter first if not training lecturer
        $paperSetter = $this->getPaperSetterForExam($examId);
        if ($paperSetter && !$this->conflictDetector->isTrainingLecturer($paperSetter['lecturer_id'])) {
            return $paperSetter['lecturer_id'];
        }

        // 2. Find available faculty member with appropriate rank
        $query = "SELECT lecturer_id FROM lecturers 
                 WHERE department = ? 
                 AND is_training_lecturer = 0
                 AND rank_level <= 3 
                 AND availability = 'Available'
                 AND NOT EXISTS (
                     SELECT 1 FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE a.supervisor_id = lecturers.lecturer_id
                     AND e.date = (SELECT date FROM exams WHERE exam_id = ?)
                     AND ((e.start_time, e.end_time) OVERLAPS (
                         SELECT start_time, end_time FROM exams WHERE exam_id = ?)
                 )
                 ORDER BY rank_level ASC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $faculty, $examId, $examId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['lecturer_id'] ?? null;
    }
}
?>