<?php
include 'db_conn.php';

$name = $_POST['emp_name'];
$salary = $_POST['emp_salary'];
$dept_id = $_POST['emp_dept_id'];

// Validate data
if (!empty($name) && !empty($salary) && !empty($dept_id)) {
    $stmt = $conn->prepare("INSERT INTO Employee (emp_name, emp_salary, emp_dept_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $salary, $dept_id);

    if ($stmt->execute()) {
        // ✅ Redirect to the view page
        header("Location: view_employee.php");
        exit();
    } else {
        echo "❌ Error inserting employee: " . $stmt->error;
    }
} else {
    echo "❌ Please fill in all fields.";
}
?>
