<?php
include 'db_conn.php';

// Check if ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $emp_id = $_GET['id'];

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM Employee WHERE emp_id = ?");
    $stmt->bind_param("i", $emp_id);

    if ($stmt->execute()) {
        // Redirect to view_employees.php after deletion
        header("Location: view_employee.php");
        exit;
    } else {
        echo "❌ Failed to delete employee: " . $stmt->error;
    }
} else {
    echo "❌ Invalid employee ID.";
}
?>
