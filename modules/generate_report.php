<?php
include '../includes/db_connect.php';

// Set header to ensure proper content type
header('Content-Type: text/html; charset=UTF-8');

// Function to format date for display
function formatDate($date) {
    return date('Y.m.d', strtotime($date));
}

// Function to format time for display
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Function to get day name from date
function getDayName($date) {
    return date('l', strtotime($date));
}

try {
    // Generate comprehensive report of exams and their allocations
    $sql = "SELECT 
                e.exam_id, 
                e.subject_name, 
                e.subject_code,
                e.date, 
                e.start_time, 
                e.end_time,
                e.student_count,
                e.repeaters,
                eh.hall_name, 
                eh.capacity,
                eh.location,
                l.name AS supervisor_name,
                l.designation AS supervisor_designation,
                GROUP_CONCAT(DISTINCT li.name SEPARATOR ', ') AS invigilators
            FROM exams e
            JOIN allocations a ON e.exam_id = a.exam_id
            JOIN exam_halls eh ON a.hall_id = eh.hall_id
            JOIN lecturers l ON a.supervisor_id = l.lecturer_id
            LEFT JOIN allocation_invigilators ai ON a.allocation_id = ai.allocation_id
            LEFT JOIN lecturers li ON ai.invigilator_id = li.lecturer_id
            GROUP BY e.exam_id
            ORDER BY e.date, e.start_time, eh.hall_name";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    // Start HTML output
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Exam Schedule Report</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                line-height: 1.6;
            }
            h1, h2 {
                color: #2c3e50;
                text-align: center;
            }
            .report-header {
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 2px solid #3498db;
            }
            .report-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
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
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #e6f7ff;
            }
            .capacity-warning {
                background-color: #ffdddd;
            }
            .print-btn {
                display: block;
                width: 150px;
                margin: 20px auto;
                padding: 10px;
                background-color: #2ecc71;
                color: white;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
            }
            .print-btn:hover {
                background-color: #27ae60;
            }
            @media print {
                .no-print {
                    display: none;
                }
                body {
                    font-size: 10pt;
                }
                table {
                    width: 100%;
                }
                th {
                    background-color: #3498db !important;
                    color: white !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <h1>Uva Wellassa University of Sri Lanka</h1>
            <h2>Exam Schedule Report</h2>
            <div class="report-info">
                <div>Generated on: ' . date('Y.m.d h:i A') . '</div>
                <div>Total Exams: ' . mysqli_num_rows($result) . '</div>
            </div>
        </div>';

    echo '<table>
            <thead>
                <tr>
                    <th>Exam ID</th>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Hall</th>
                    <th>Capacity</th>
                    <th>Students</th>
                    <th>Supervisor</th>
                    <th>Invigilators</th>
                </tr>
            </thead>
            <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $totalStudents = $row['student_count'] + $row['repeaters'];
        $capacityClass = ($totalStudents > $row['capacity']) ? 'capacity-warning' : '';
        
        echo '<tr class="' . $capacityClass . '">
                <td>' . htmlspecialchars($row['exam_id']) . '</td>
                <td>' . htmlspecialchars($row['subject_name']) . '</td>
                <td>' . htmlspecialchars($row['subject_code']) . '</td>
                <td>' . formatDate($row['date']) . '</td>
                <td>' . getDayName($row['date']) . '</td>
                <td>' . formatTime($row['start_time']) . ' - ' . formatTime($row['end_time']) . '</td>
                <td>' . htmlspecialchars($row['hall_name']) . '<br><small>' . htmlspecialchars($row['location']) . '</small></td>
                <td>' . $row['capacity'] . '</td>
                <td>' . $totalStudents . ' (P: ' . $row['student_count'] . ', R: ' . $row['repeaters'] . ')</td>
                <td>' . htmlspecialchars($row['supervisor_name']) . '<br><small>' . htmlspecialchars($row['supervisor_designation']) . '</small></td>
                <td>' . htmlspecialchars($row['invigilators'] ?? 'None') . '</td>
              </tr>';
    }

    echo '</tbody></table>';

    // Add print button
    echo '<a href="#" class="print-btn no-print" onclick="window.print()">Print Report</a>';

    echo '</body></html>';

} catch (Exception $e) {
    // Error handling
    echo '<div style="color: red; padding: 20px; border: 1px solid red; margin: 20px;">
            <h2>Error Generating Report</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}

// Close database connection
mysqli_close($conn);
?>