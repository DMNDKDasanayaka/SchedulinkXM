<?php
class HallModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all exam halls
     * @return array List of halls
     */
    public function getAllHalls() {
        $stmt = $this->db->query("SELECT * FROM halls ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single hall by ID
     * @param int $id Hall ID
     * @return array|null Hall data or null if not found
     */
    public function getHallById($id) {
        $stmt = $this->db->prepare("SELECT * FROM halls WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a new hall
     * @param array $data Hall data (name, capacity, faculty)
     * @return bool True on success, false on failure
     */
    public function createHall($data) {
        $stmt = $this->db->prepare("INSERT INTO halls 
            (name, capacity, faculty) 
            VALUES (:name, :capacity, :faculty)");
        
        return $stmt->execute([
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'faculty' => $data['faculty'] ?? null
        ]);
    }

    /**
     * Update an existing hall
     * @param array $data Hall data including ID
     * @return bool True on success, false on failure
     */
    public function updateHall($data) {
        $stmt = $this->db->prepare("UPDATE halls SET 
            name = :name,
            capacity = :capacity,
            faculty = :faculty,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        
        return $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'faculty' => $data['faculty'] ?? null
        ]);
    }

    /**
     * Delete a hall
     * @param int $id Hall ID
     * @return bool True on success, false on failure
     */
    public function deleteHall($id) {
        $stmt = $this->db->prepare("DELETE FROM halls WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if a hall is assigned to any exams
     * @param int $hallId Hall ID
     * @return bool True if assigned, false otherwise
     */
    public function isHallAssigned($hallId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM allocations WHERE hall_id = :hall_id");
        $stmt->execute(['hall_id' => $hallId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check hall availability for a given time slot
     * @param int $hallId Hall ID
     * @param string $date Date (YYYY-MM-DD)
     * @param string $startTime Start time (HH:MM:SS)
     * @param string $endTime End time (HH:MM:SS)
     * @return bool True if available, false if booked
     */
    public function checkAvailability($hallId, $date, $startTime, $endTime) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM allocations a
            JOIN exams e ON a.exam_id = e.id
            WHERE a.hall_id = :hall_id
            AND e.date = :date
            AND (
                (e.start_time < :end_time AND e.end_time > :start_time)
            )
        ");
        
        $stmt->execute([
            'hall_id' => $hallId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Get halls with sufficient capacity for given student count
     * @param int $studentCount Number of students
     * @param string $date Date (YYYY-MM-DD)
     * @param string $startTime Start time (HH:MM:SS)
     * @param string $endTime End time (HH:MM:SS)
     * @return array List of available halls
     */
    public function getAvailableHalls($studentCount, $date = null, $startTime = null, $endTime = null) {
        $query = "SELECT * FROM halls WHERE capacity >= :capacity";
        $params = ['capacity' => $studentCount];

        // Add availability check if time parameters are provided
        if ($date && $startTime && $endTime) {
            $query .= " AND id NOT IN (
                SELECT hall_id FROM allocations a
                JOIN exams e ON a.exam_id = e.id
                WHERE e.date = :date
                AND (e.start_time < :end_time AND e.end_time > :start_time)
            )";
            $params['date'] = $date;
            $params['start_time'] = $startTime;
            $params['end_time'] = $endTime;
        }

        $query .= " ORDER BY capacity ASC"; // Prefer halls with just enough capacity

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get halls by faculty
     * @param string $faculty Faculty code
     * @return array List of halls
     */
    public function getHallsByFaculty($faculty) {
        $stmt = $this->db->prepare("SELECT * FROM halls WHERE faculty = :faculty ORDER BY name ASC");
        $stmt->execute(['faculty' => $faculty]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total hall capacity
     * @return int Total capacity across all halls
     */
    public function getTotalCapacity() {
        $stmt = $this->db->query("SELECT SUM(capacity) FROM halls");
        return (int)$stmt->fetchColumn();
    }
}
?>