<?php
require_once __DIR__ . '/../includes/db_connect.php';

class PaymentCalculator {
    private $conn;
    private $currentRates = [
        'supervisor' => 1500,
        'invigilator' => 1000,
        'hall_attendant' => 800
    ];

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->loadCurrentRates();
    }

    private function loadCurrentRates() {
        try {
            $query = "SELECT role_type, rate_amount FROM pay_rates 
                     WHERE effective_date <= CURDATE() 
                     ORDER BY effective_date DESC";
            $result = $this->conn->query($query);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $this->currentRates[$row['role_type']] = $row['rate_amount'];
                }
            }
        } catch (Exception $e) {
            error_log("Error loading payment rates: " . $e->getMessage());
        }
    }

    public function calculatePaymentForAllocation($allocationId) {
        try {
            $query = "SELECT a.*, e.subject_name, l.name AS lecturer_name 
                     FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     JOIN lecturers l ON a.supervisor_id = l.lecturer_id
                     WHERE a.allocation_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $allocationId);
            $stmt->execute();
            $allocation = $stmt->get_result()->fetch_assoc();

            if (!$allocation) {
                return ['error' => 'Allocation not found'];
            }

            // Supervisor payment
            $supervisorPayment = [
                'lecturer_id' => $allocation['supervisor_id'],
                'allocation_id' => $allocationId,
                'role_type' => 'supervisor',
                'payment_amount' => $this->currentRates['supervisor'],
                'exam_details' => $allocation['subject_name']
            ];

            // Invigilator payments
            $invigilatorPayments = [];
            $invigilatorIds = json_decode($allocation['invigilator_ids'] ?? '[]', true);
            
            foreach ($invigilatorIds as $invigilatorId) {
                $invigilatorPayments[] = [
                    'lecturer_id' => $invigilatorId,
                    'allocation_id' => $allocationId,
                    'role_type' => 'invigilator',
                    'payment_amount' => $this->currentRates['invigilator'],
                    'exam_details' => $allocation['subject_name']
                ];
            }

            return [
                'supervisor' => $supervisorPayment,
                'invigilators' => $invigilatorPayments
            ];
        } catch (Exception $e) {
            error_log("Payment calculation error: " . $e->getMessage());
            return ['error' => 'Failed to calculate payment'];
        }
    }

    public function processBatchPayments($startDate, $endDate) {
        $this->conn->begin_transaction();
        try {
            $query = "SELECT allocation_id FROM allocations a
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $processed = 0;
            $errors = 0;
            
            while ($row = $result->fetch_assoc()) {
                $payments = $this->calculatePaymentForAllocation($row['allocation_id']);
                if (!isset($payments['error'])) {
                    if ($this->savePayments($payments)) {
                        $processed++;
                    } else {
                        $errors++;
                    }
                } else {
                    $errors++;
                }
            }
            
            $this->conn->commit();
            return [
                'success' => true,
                'processed' => $processed,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Batch payment processing failed: " . $e->getMessage());
            return ['error' => 'Batch processing failed'];
        }
    }

    private function savePayments($payments) {
        try {
            // Insert supervisor payment
            $this->insertPayment($payments['supervisor']);
            
            // Insert invigilator payments
            if (isset($payments['invigilators']) && is_array($payments['invigilators'])) {
                foreach ($payments['invigilators'] as $invigilatorPayment) {
                    $this->insertPayment($invigilatorPayment);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Payment save failed: " . $e->getMessage());
            return false;
        }
    }

    private function insertPayment($payment) {
        $query = "INSERT INTO duty_payments 
                 (lecturer_id, allocation_id, role_type, payment_amount, payment_status)
                 VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iisd",
            $payment['lecturer_id'],
            $payment['allocation_id'],
            $payment['role_type'],
            $payment['payment_amount']
        );
        $stmt->execute();
    }

    public function generatePaymentReport($startDate, $endDate) {
        try {
            $query = "SELECT dp.*, l.name AS lecturer_name, e.subject_name, e.date
                     FROM duty_payments dp
                     JOIN lecturers l ON dp.lecturer_id = l.lecturer_id
                     JOIN allocations a ON dp.allocation_id = a.allocation_id
                     JOIN exams e ON a.exam_id = e.exam_id
                     WHERE e.date BETWEEN ? AND ?
                     ORDER BY l.name, e.date";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $payments = [];
            
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            return $payments;
        } catch (Exception $e) {
            error_log("Report generation error: " . $e->getMessage());
            return [];
        }
    }
}
?>