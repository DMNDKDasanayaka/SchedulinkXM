<?php
// Database connection
$db = new mysqli('localhost', 'root', '', 'schedulinkxm');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Function to get department from degree program
function getDepartmentFromDegree($degree) {
    $degree = preg_replace('/\(.*\)/', '', $degree); // Remove parenthetical text
    $degree = trim($degree);
    
    $mapping = [
        'ANS' => 'ANS',
        'AQT' => 'AQT',
        'EAG' => 'EAG',
        'FST' => 'FST',
        'TEA' => 'EAG',
        'PLT' => 'EAG',
        'APT' => 'EAG',
        'FPT' => 'FST',
        'ETA' => 'EAG'
    ];
    return $mapping[$degree] ?? 'EAG';
}

// Function to extract subject code
function extractSubjectCode($subject) {
    preg_match('/\(([A-Z]+\s*\d+-\d+)\)/', $subject, $matches);
    return $matches[1] ?? substr($subject, 0, 3);
}

// Function to assign staff with priority to examiners
function assignStaff($db, $subject, $degree, $role, $date, $time) {
    $subjectCode = extractSubjectCode($subject);
    $dept = getDepartmentFromDegree($degree);
    
    // First try to get examiners for this subject
    $examinerType = ($role == 'supervisor') ? 'first_examiner' : 'second_examiner';
    $query = "SELECT e.$examinerType as name 
              FROM examiners e 
              WHERE e.code LIKE ?";
    $stmt = $db->prepare($query);
    $likeCode = "%$subjectCode%";
    $stmt->bind_param("s", $likeCode);
    $stmt->execute();
    $examiners = $stmt->get_result();
    
    while ($examiner = $examiners->fetch_assoc()) {
        $name = $examiner['name'];
        // Check if available
        $check = $db->prepare("SELECT 1 FROM lecturers 
                              WHERE name = ? AND availability = 'Available'
                              AND NOT EXISTS (
                                  SELECT 1 FROM duty_roster dr 
                                  WHERE (dr.supervisor = ? OR dr.hall_attendant = ?)
                                  AND dr.date = ? AND dr.time = ?
                              )");
        $check->bind_param("sssss", $name, $name, $name, $date, $time);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return $name;
        }
    }
    
    // If no available examiners, try department staff
    $query = "SELECT l.name, 
              (SELECT COUNT(*) FROM duty_roster dr 
               WHERE (dr.supervisor = l.name OR dr.hall_attendant = l.name)
               AND dr.date = ? AND dr.time = ?) as already_assigned,
              (SELECT COUNT(*) FROM duty_roster dr 
               WHERE dr.supervisor = l.name OR dr.hall_attendant = l.name) as workload
              FROM lecturers l
              WHERE l.availability = 'Available'
              AND l.department = ?
              AND NOT EXISTS (
                  SELECT 1 FROM duty_roster dr 
                  WHERE (dr.supervisor = l.name OR dr.hall_attendant = l.name)
                  AND dr.date = ? AND dr.time = ?
              )
              ORDER BY already_assigned, workload ASC, RAND()
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("sssss", $date, $time, $dept, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['name'];
    }
    
    // Fallback to any available staff
    $query = "SELECT l.name, 
              (SELECT COUNT(*) FROM duty_roster dr 
               WHERE dr.supervisor = l.name OR dr.hall_attendant = l.name) as workload
              FROM lecturers l
              WHERE l.availability = 'Available'
              AND NOT EXISTS (
                  SELECT 1 FROM duty_roster dr 
                  WHERE (dr.supervisor = l.name OR dr.hall_attendant = l.name)
                  AND dr.date = ? AND dr.time = ?
              )
              ORDER BY workload ASC, RAND()
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['name'];
    }
    
    // Last resort - any staff member
    $query = "SELECT name FROM lecturers ORDER BY RAND() LIMIT 1";
    $result = $db->query($query);
    return $result->fetch_assoc()['name'];
}

// Process form submission for generating roster
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    // Clear existing roster
    $db->query("TRUNCATE TABLE duty_roster");
    $db->query("TRUNCATE TABLE allocations");
    $db->query("TRUNCATE TABLE duty_payments");
    
    // Get all exams from the database
    $exams = $db->query("SELECT * FROM exams ORDER BY date, start_time");
    
    while ($exam = $exams->fetch_assoc()) {
        // Auto-assign staff with priority to examiners
        $supervisor = assignStaff($db, $exam['subject_name'], $exam['degree'], 'supervisor', $exam['date'], $exam['start_time']);
        $attendant = assignStaff($db, $exam['subject_name'], $exam['degree'], 'hall_attendant', $exam['date'], $exam['start_time']);
        
        // Format time for display
        $start_time = date("h.i A", strtotime($exam['start_time']));
        $end_time = date("h.i A", strtotime($exam['end_time']));
        $time_slot = "$start_time - $end_time";
        
        // Get day of week
        $day = date('l', strtotime($exam['date']));
        
        // Insert into roster
        $stmt = $db->prepare("INSERT INTO duty_roster 
            (date, day, time, subject, degree, venue, supervisor, hall_attendant, p_count, r_count, total) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssiii", 
            $exam['date'],
            $day,
            $time_slot,
            $exam['subject_name'],
            $exam['degree'],
            $exam['faculty'], // Using faculty as venue in this example
            $supervisor,
            $attendant,
            $exam['student_count'],
            $exam['repeaters'],
            $exam['student_count'] + $exam['repeaters']
        );
        $stmt->execute();
        $rosterId = $db->insert_id;
        
        // Get lecturer IDs
        $supervisorId = getLecturerId($db, $supervisor);
        $attendantId = getLecturerId($db, $attendant);
        $examId = $exam['exam_id'];
        $hallId = getHallId($db, $exam['faculty']); // Using faculty as hall name in this example
        
        // Create allocation record
        if ($supervisorId && $examId && $hallId) {
            $allocStmt = $db->prepare("INSERT INTO allocations 
                (exam_id, hall_id, supervisor_id) 
                VALUES (?, ?, ?)");
            $allocStmt->bind_param("iii", $examId, $hallId, $supervisorId);
            $allocStmt->execute();
            $allocationId = $db->insert_id;
            
            // Record payment for supervisor
            $paymentStmt = $db->prepare("INSERT INTO duty_payments 
                (lecturer_id, allocation_id, role_type, payment_amount, payment_status) 
                VALUES (?, ?, 'supervisor', (SELECT rate_amount FROM pay_rates WHERE role_type = 'supervisor' ORDER BY effective_date DESC LIMIT 1), 'pending')");
            $paymentStmt->bind_param("ii", $supervisorId, $allocationId);
            $paymentStmt->execute();
            
            // Record payment for hall attendant
            if ($attendantId) {
                $paymentStmt = $db->prepare("INSERT INTO duty_payments 
                    (lecturer_id, allocation_id, role_type, payment_amount, payment_status) 
                    VALUES (?, ?, 'hall_attendant', (SELECT rate_amount FROM pay_rates WHERE role_type = 'hall_attendant' ORDER BY effective_date DESC LIMIT 1), 'pending')");
                $paymentStmt->bind_param("ii", $attendantId, $allocationId);
                $paymentStmt->execute();
            }
        }
    }
    $success = "Duty roster generated successfully with intelligent assignments!";
}

// Helper function to get lecturer ID
function getLecturerId($db, $name) {
    $stmt = $db->prepare("SELECT lecturer_id FROM lecturers WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['lecturer_id'] : null;
}

// Helper function to get hall ID
function getHallId($db, $venue) {
    $stmt = $db->prepare("SELECT hall_id FROM exam_halls WHERE hall_name = ? LIMIT 1");
    $stmt->bind_param("s", $venue);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['hall_id'] : null;
}

// Fetch duty roster data
$roster = $db->query("SELECT * FROM duty_roster ORDER BY date, time");

// Fetch all lecturers for reference
$lecturers = $db->query("SELECT * FROM lecturers");
$lecturerList = [];
while ($lecturer = $lecturers->fetch_assoc()) {
    $lecturerList[$lecturer['name']] = $lecturer;
}

// Helper function to check if staff is subject expert
function isSubjectExpert($db, $name, $subjectCode) {
    $query = "SELECT 1 FROM examiners 
             WHERE (first_examiner = ? OR second_examiner = ? OR setter_moderator = ?)
             AND code LIKE ?";
    $stmt = $db->prepare($query);
    $likeCode = "%$subjectCode%";
    $stmt->bind_param("ssss", $name, $name, $name, $likeCode);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Helper function to check department match
function isDepartmentMatch($name, $degree, $lecturerList) {
    if (!isset($lecturerList[$name])) return false;
    
    $dept = getDepartmentFromDegree($degree);
    return $lecturerList[$name]['department'] == $dept;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automatic Duty Roster Generator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; }
        .controls { 
            margin: 20px 0; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 5px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        button { 
            background-color: #3498db; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            margin: 5px; 
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        button:hover { background-color: #2980b9; }
        .print-btn { background-color: #2ecc71; }
        .print-btn:hover { background-color: #27ae60; }
        .generate-btn { background-color: #e67e22; }
        .generate-btn:hover { background-color: #d35400; }
        .success { 
            color: #155724; 
            background-color: #d4edda; 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px;
            text-align: center;
        }
        .error { 
            color: #721c24; 
            background-color: #f8d7da; 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px;
            text-align: center;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        }
        th { 
            background-color: #3498db; 
            color: white; 
            position: sticky; 
            top: 0; 
        }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e6f7ff; }
        .date-group { 
            background-color: #ecf0f1; 
            font-weight: bold; 
            font-size: 1.1em;
        }
        .subject-expert { background-color: #e6ffe6; }
        .department-match { background-color: #fff3cd; }
        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        @media print {
            .no-print { display: none; }
            body { font-size: 10pt; }
            table { width: 100%; }
            th { background-color: #3498db !important; color: white !important; }
        }
    </style>
</head>
<body>
    <h1>Automatic Duty Roster Generator</h1>
    
    <div class="controls no-print">
        <form method="POST">
            <button type="submit" name="generate" class="generate-btn">Generate New Roster</button>
        </form>
        <button type="button" class="print-btn" onclick="window.print()">Print Roster</button>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Time</th>
                <th>Subject</th>
                <th>Degree</th>
                <th>Venue</th>
                <th>Supervisor</th>
                <th>Hall Attendant</th>
                <th>Students (P)</th>
                <th>Students (R)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $currentDate = null;
            if ($roster && $roster->num_rows > 0) {
                while ($row = $roster->fetch_assoc()):
                    if ($currentDate !== $row['date']) {
                        $currentDate = $row['date'];
                        echo '<tr class="date-group">
                            <td colspan="11">' . date('Y.m.d', strtotime($row['date'])) . ' ' . $row['day'] . '</td>
                        </tr>';
                    }
                    
                    // Check if staff are subject experts or department matches
                    $subjectCode = extractSubjectCode($row['subject']);
                    $supervisorClass = '';
                    $attendantClass = '';
                    $supervisorTitle = '';
                    $attendantTitle = '';
                    
                    // Check supervisor
                    $isSubjectExpert = isSubjectExpert($db, $row['supervisor'], $subjectCode);
                    $deptMatch = isDepartmentMatch($row['supervisor'], $row['degree'], $lecturerList);
                    
                    if ($isSubjectExpert) {
                        $supervisorClass = 'subject-expert';
                        $supervisorTitle = 'Subject Examiner/Moderator';
                    } elseif ($deptMatch) {
                        $supervisorClass = 'department-match';
                        $supervisorTitle = 'Department Match';
                    } else {
                        $supervisorTitle = 'General Assignment';
                    }
                    
                    // Check attendant
                    $attendantDeptMatch = isDepartmentMatch($row['hall_attendant'], $row['degree'], $lecturerList);
                    if ($attendantDeptMatch) {
                        $attendantClass = 'department-match';
                        $attendantTitle = 'Department Match';
                    } else {
                        $attendantTitle = 'General Assignment';
                    }
                ?>
                <tr>
                    <td><?= date('Y.m.d', strtotime($row['date'])) ?></td>
                    <td><?= $row['day'] ?></td>
                    <td><?= $row['time'] ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= htmlspecialchars($row['degree']) ?></td>
                    <td><?= htmlspecialchars($row['venue']) ?></td>
                    <td class="<?= $supervisorClass ?> tooltip">
                        <?= htmlspecialchars($row['supervisor']) ?>
                        <span class="tooltiptext"><?= $supervisorTitle ?></span>
                    </td>
                    <td class="<?= $attendantClass ?> tooltip">
                        <?= htmlspecialchars($row['hall_attendant']) ?>
                        <span class="tooltiptext"><?= $attendantTitle ?></span>
                    </td>
                    <td><?= $row['p_count'] ?></td>
                    <td><?= $row['r_count'] ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
                <?php endwhile;
            } else {
                echo '<tr><td colspan="11" style="text-align: center;">No duty roster found. Please generate a roster.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <?php $db->close(); ?>
</body>
</html>