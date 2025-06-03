<?php
require_once 'db.php';

// Add google_id column to users table if it doesn't exist
try {
    // Check if the column already exists
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($check->rowCount() == 0) {
        // Column doesn't exist, add it
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER password");
        echo "Successfully added google_id column to users table.";
    } else {
        echo "google_id column already exists in users table.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

