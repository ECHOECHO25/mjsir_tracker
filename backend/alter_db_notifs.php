<?php
$conn = new mysqli("localhost", "root", "", "mjsir_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_user VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table notifications created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
