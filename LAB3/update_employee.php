<?php
include 'db_conn.php';

// Check if all required POST values are set
if (isset($_POST['emp_id'], $_POST['emp_name'], $_POST['emp_salary'], $_POST['emp_dept_id'])) {
    $id    = $_POST['emp_id'];
    $name  = $_POST['emp_name'];
    $salary = $_POST['emp_salary'];
    $dept  = $_POST['emp_dept_id'];

    // Prepare and execute the SQL update statement
    $stmt = $conn->prepare("UPDATE Employee SET emp_name = ?, emp_salary = ?, emp_dept_id = ? WHERE emp_id = ?");
    $stmt->bind_param("sdii", $name, $salary, $dept, $id);

    if ($stmt->execute()) {
        // Redirect after success
        header("Location: view_employee.php");
        exit;
    } else {
        echo "❌ Update failed: " . $stmt->error;
    }
} else {
    echo "❌ Missing form data. Please ensure all fields are filled.";
}
?>
