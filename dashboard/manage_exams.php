<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP username
define('DB_PASS', '');           // Default XAMPP password (empty)
define('DB_NAME', 'schedulinkxm'); // Your database name

// Establish database connection
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    // Set charset to utf8
    $db->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            // Add new examiner
            $stmt = $db->prepare("INSERT INTO examiners (no, code, title, setter_moderator, first_examiner, second_examiner, degree_programs) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $_POST['no'], $_POST['code'], $_POST['title'], $_POST['setter_moderator'], $_POST['first_examiner'], $_POST['second_examiner'], $_POST['degree_programs']);
            $stmt->execute();
            $success = "Examiner added successfully!";
            
        } elseif (isset($_POST['update'])) {
            // Update examiner
            $stmt = $db->prepare("UPDATE examiners SET no=?, code=?, title=?, setter_moderator=?, first_examiner=?, second_examiner=?, degree_programs=? WHERE id=?");
            $stmt->bind_param("issssssi", $_POST['no'], $_POST['code'], $_POST['title'], $_POST['setter_moderator'], $_POST['first_examiner'], $_POST['second_examiner'], $_POST['degree_programs'], $_POST['id']);
            $stmt->execute();
            $success = "Examiner updated successfully!";
            header("Location: manage_examiners.php"); // Redirect to clear POST data
            exit();
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
} elseif (isset($_GET['delete'])) {
    try {
        // Delete examiner
        $stmt = $db->prepare("DELETE FROM examiners WHERE id=?");
        $stmt->bind_param("i", $_GET['delete']);
        $stmt->execute();
        $success = "Examiner deleted successfully!";
        header("Location: manage_examiners.php");
        exit();
    } catch (Exception $e) {
        $error = "Error deleting record: " . $e->getMessage();
    }
}

// Fetch data for editing if edit parameter is set
$editData = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM examiners WHERE id=?");
        $stmt->bind_param("i", $_GET['edit']);
        $stmt->execute();
        $result = $stmt->get_result();
        $editData = $result->fetch_assoc();
    } catch (Exception $e) {
        $error = "Error fetching record: " . $e->getMessage();
    }
}

// Fetch all examiners
try {
    $examiners = $db->query("SELECT * FROM examiners ORDER BY no");
} catch (Exception $e) {
    $error = "Error fetching examiners: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Examiners</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; background: #f9f9f9; padding: 20px; border-radius: 5px; }
        input, button { padding: 8px; margin: 5px 0; }
        .form-group { margin-bottom: 10px; }
        .action-btn { margin-right: 5px; text-decoration: none; padding: 5px 10px; }
        .success { color: green; background: #e6ffe6; padding: 10px; }
        .error { color: red; background: #ffebeb; padding: 10px; }
    </style>
</head>
<body>
    <h1>Manage Examiners</h1>
    
    <!-- Display success/error messages -->
    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Form -->
    <h2><?= isset($_GET['edit']) ? 'Edit Examiner' : 'Add New Examiner' ?></h2>
    <form method="POST">
        <?php if (isset($editData)): ?>
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>No:</label>
            <input type="number" name="no" value="<?= $editData['no'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Code:</label>
            <input type="text" name="code" value="<?= $editData['code'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" value="<?= $editData['title'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Setter/Moderator:</label>
            <input type="text" name="setter_moderator" value="<?= $editData['setter_moderator'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>First Examiner:</label>
            <input type="text" name="first_examiner" value="<?= $editData['first_examiner'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Second Examiner:</label>
            <input type="text" name="second_examiner" value="<?= $editData['second_examiner'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Degree Programs:</label>
            <input type="text" name="degree_programs" value="<?= $editData['degree_programs'] ?? '' ?>" required>
        </div>
        
        <button type="submit" name="<?= isset($editData) ? 'update' : 'add' ?>">
            <?= isset($editData) ? 'Update' : 'Add' ?> Examiner
        </button>
        
        <?php if (isset($editData)): ?>
            <a href="manage_examiners.php" class="action-btn">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Examiners List -->
    <h2>Examiners List</h2>
    <?php if ($examiners && $examiners->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code</th>
                    <th>Title</th>
                    <th>Setter/Moderator</th>
                    <th>First Examiner</th>
                    <th>Second Examiner</th>
                    <th>Degree Programs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $examiners->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['no']) ?></td>
                        <td><?= htmlspecialchars($row['code']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['setter_moderator']) ?></td>
                        <td><?= htmlspecialchars($row['first_examiner']) ?></td>
                        <td><?= htmlspecialchars($row['second_examiner']) ?></td>
                        <td><?= htmlspecialchars($row['degree_programs']) ?></td>
                        <td>
                            <a href="manage_examiners.php?edit=<?= $row['id'] ?>" class="action-btn">Edit</a>
                            <a href="manage_examiners.php?delete=<?= $row['id'] ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this examiner?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No examiners found.</p>
    <?php endif; ?>

    <?php $db->close(); ?>
</body>
</html>